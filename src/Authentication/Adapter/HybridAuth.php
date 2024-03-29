<?php
namespace PlaygroundUser\Authentication\Adapter;

use Hybrid_Auth;
use PlaygroundUser\Mapper\UserProvider;
use PlaygroundUser\Options\ModuleOptions;
use Laminas\Authentication\Result;
use Laminas\ServiceManager\ServiceManager;
use LmcUser\Authentication\Adapter\AbstractAdapter;
use LmcUser\Mapper\UserInterface as UserMapperInterface;
use LmcUser\Options\UserServiceOptionsInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use LmcUser\Authentication\Adapter\AdapterChainEvent;

class HybridAuth extends AbstractAdapter implements EventManagerAwareInterface
{
    /**
     * @var Hybrid_Auth
     */
    protected $hybridAuth;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $lmcuserOptions;

    /**
     * @var UserProviderInterface
     */
    protected $mapper;

    /**
     * @var UserMapperInterface
     */
    protected $lmcuserMapper;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var RoleMapperInterface
     */
    protected $roleMapper;

    public function authenticate(AdapterChainEvent $e)
    {
        if ($this->isSatisfied()) {
            $storage = $this->getStorage()->read();
            $e->setIdentity($storage['identity'])
              ->setCode(Result::SUCCESS)
              ->setMessages(array('Authentication successful.'));

            return;
        }

        $provider = $e->getRequest()->getQuery()->get('provider');
        if (empty($provider)) {
            return;
        }

        $enabledProviders = $this->getOptions()->getEnabledProviders();
        if (!in_array($provider, $enabledProviders)) {
            $e->setCode(Result::FAILURE)
              ->setMessages(array('Invalid provider'));
            $this->setSatisfied(false);
            return false;
        }

        $userProfile = null;
        try {
            $adapter = $this->getHybridAuth()->authenticate($provider);
            if ($adapter->isConnected()) {
                $userProfile = $adapter->getUserProfile();
            }
        } catch (\Exception $ex) {
            // The following retry is efficient in case a user previously registered on his social account
            // with the app has unsubsribed from the app
            // cf http://hybridauth.sourceforge.net/userguide/HybridAuth_Sessions.html

            if (($ex->getCode() == 6) || ($ex->getCode() == 7)) {
                // Réinitialiser la session HybridAuth
                $this->getHybridAuth()->getAdapter($provider)->logout();
                // Essayer de se connecter à nouveau
                $adapter = $this->getHybridAuth()->authenticate($provider);
                if ($adapter->isConnected()) {
                    $userProfile = $adapter->getUserProfile();
                }
            } else {
                $e->setCode(Result::FAILURE)
                ->setMessages(array('Invalid provider'));
                $this->setSatisfied(false);

                return false;
            }
        }

        if (!$userProfile) {
            $e->setCode(Result::FAILURE_IDENTITY_NOT_FOUND)
              ->setMessages(array('A record with the supplied identity could not be found.'));
            $this->setSatisfied(false);

            return false;
        }

        $localUserProvider = $this->getMapper()->findUserByProviderId($userProfile->identifier, $provider);

        if (false == $localUserProvider && $this->getOptions()->getCreateUserAutoSocial()) {
            $method = $provider.'ToLocalUser';
            if (method_exists($this, $method)) {
                try {
                    $localUser = $this->$method($userProfile);
                } catch (\RuntimeException $ex) {
                    $e->setCode($ex->getCode())
                        ->setMessages(array($ex->getMessage()))
                        ->stopPropagation();
                    $this->setSatisfied(false);

                    return false;
                }
            } else {
                $localUser = $this->instantiateLocalUser();
                $localUser->setDisplayName($userProfile->displayName)
                    ->setPassword($provider)
                    ->setEmail($userProfile->email);
                $this->insert($localUser, 'other', $userProfile);
            }

            $localUserProvider = new \PlaygroundUser\Entity\UserProvider();
            $localUserProvider->setUser($localUser)
                ->setProviderId($userProfile->identifier)
                ->setProvider($provider);
            $this->getMapper()->insert($localUserProvider);
        }

        $lmcuserOptions = $this->getLmcUserOptions();

        if ($lmcuserOptions->getEnableUserState()) {
            // Don't allow user to login if state is not in allowed list
            $mapper = $this->getLmcUserMapper();
            $user = $mapper->findById($localUserProvider->getUser()->getId());
            if (!in_array($user->getState(), $lmcuserOptions->getAllowedLoginStates())) {
                $e->setCode(Result::FAILURE_UNCATEGORIZED)
                    ->setMessages(array('A record with the supplied identity is not active.'));
                $this->setSatisfied(false);

                return false;
            }
        }

        $e->setIdentity($localUserProvider->getUser()->getId());

        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(Result::SUCCESS)->setMessages(array('Authentication successful.'));
    }

    /**
     * Get the Hybrid_Auth object
     *
     * @return Hybrid_Auth
     */
    public function getHybridAuth()
    {
        if (!$this->hybridAuth) {
            $this->hybridAuth = $this->getServiceManager()->get('HybridAuth');
        }

        return $this->hybridAuth;
    }

    /**
     * Set the Hybrid_Auth object
     *
     * @param  Hybrid_Auth    $hybridAuth
     * @return UserController
     *
     * CAUTION : the type of the parameter is Hybrid_Auth but can be NULL if an error is catched during
     * the creation of the object in the factory (ie. FB : I refuse the options given on registration => error)
     * In this type of case, getHybridAuth use the serviceManager to grab a new HybridAuth instance
     */
    public function setHybridAuth($hybridAuth)
    {
        $this->hybridAuth = $hybridAuth;

        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return void
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * set options
     *
     * @param  ModuleOptions $options
     * @return HybridAuth
     */
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * get options
     *
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }

    /**
     * @param  UserServiceOptionsInterface $options
     * @return HybridAuth
     */
    public function setLmcUserOptions(UserServiceOptionsInterface $options)
    {
        $this->lmcuserOptions = $options;

        return $this;
    }

    /**
     * @return UserServiceOptionsInterface
     */
    public function getLmcUserOptions()
    {
        if (!$this->lmcuserOptions instanceof UserServiceOptionsInterface) {
            $this->setLmcUserOptions($this->getServiceManager()->get('lmcuser_module_options'));
        }

        return $this->lmcuserOptions;
    }

    /**
     * set mapper
     *
     * @param  UserProvider $mapper
     * @return HybridAuth
     */
    public function setMapper(UserProvider $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * get mapper
     *
     * @return UserProviderInterface
     */
    public function getMapper()
    {
        if (!$this->mapper instanceof UserProvider) {
            $this->setMapper($this->getServiceManager()->get('playgrounduser_userprovider_mapper'));
        }

        return $this->mapper;
    }

    /**
     * set lmcuserMapper
     *
     * @param  UserMapperInterface $lmcuserMapper
     * @return HybridAuth
     */
    public function setLmcUserMapper(UserMapperInterface $lmcuserMapper)
    {
        $this->lmcuserMapper = $lmcuserMapper;

        return $this;
    }

    /**
     * get lmcuserMapper
     *
     * @return UserMapperInterface
     */
    public function getLmcUserMapper()
    {
        if (!$this->lmcuserMapper instanceof UserMapperInterface) {
            $this->setLmcUserMapper($this->getServiceManager()->get('lmcuser_user_mapper'));
        }

        return $this->lmcuserMapper;
    }

    /**
     * Utility function to instantiate a fresh local user object
     *
     * @return mixed
     */
    protected function instantiateLocalUser()
    {
        $userModelClass = $this->getLmcUserOptions()->getUserEntityClass();

        return new $userModelClass;
    }

    // Provider specific methods

    protected function facebookToLocalUser($userProfile)
    {
        $mapper = $this->getLmcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->email))) {
            return $localUser;
        }
        $localUser = $this->instantiateLocalUser();

        $userName = ucfirst($userProfile->firstName) . " " . substr(ucfirst($userProfile->lastName), 0, 1);

        $birthDay = null;
        if ($userProfile->birthDay && $userProfile->birthMonth && $userProfile->birthYear) {
            $birthDay = new \DateTime(
                (string)$userProfile->birthYear.'-'
                .(string)$userProfile->birthMonth.'-'
                .(string)$userProfile->birthDay
            );
        }

        $localUser->setEmail($userProfile->email)
            ->setDisplayName($userProfile->displayName)
            ->setFirstname($userProfile->firstName)
            ->setLastname($userProfile->lastName)
            ->setGender($userProfile->gender)
            ->setMobile($userProfile->phone)
            ->setAddress($userProfile->address)
            ->setPostalCode($userProfile->zip)
            ->setCity($userProfile->city)
            ->setCountry($userProfile->country)
            ->setUsername($userName)
            ->setAvatar($userProfile->photoURL)
            ->setPassword(__FUNCTION__);
        if ($birthDay) {
            $localUser->setDob($birthDay);
        }

        $this->insert($localUser, 'facebook', $userProfile);

        return $localUser;
    }

    protected function foursquareToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new \RuntimeException(
                'Please verify your email with Foursquare before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }
        $mapper = $this->getLmcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }
        $localUser = $this->instantiateLocalUser();
        $localUser->setEmail($userProfile->emailVerified)
            ->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $this->insert($localUser, 'foursquare', $userProfile);

        return $localUser;
    }

    protected function googleToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new \RuntimeException(
                'Please verify your email with Google before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }
        $mapper = $this->getLmcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }
        $localUser = $this->instantiateLocalUser();
        $localUser->setEmail($userProfile->emailVerified)
            ->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $this->insert($localUser, 'google', $userProfile);

        return $localUser;
    }

    protected function linkedInToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $this->insert($localUser, 'linkedIn', $userProfile);

        return $localUser;
    }

    protected function twitterToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setUsername($userProfile->displayName)
            ->setDisplayName($userProfile->firstName)
            ->setPassword(__FUNCTION__);
        $this->insert($localUser, 'twitter', $userProfile);

        return $localUser;
    }

    protected function yahooToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $this->insert($localUser, 'yahoo', $userProfile);

        return $localUser;
    }

    protected function githubToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setDisplayName($userProfile->displayName)
                  ->setPassword(__FUNCTION__)
                  ->setEmail($userProfile->email);

        $this->getEventManager()->trigger(__FUNCTION__, $localUser, array('userProfile' => $userProfile));

        $this->insert($localUser, 'github', $userProfile);

        return $localUser;
    }

    /**
     * persists the user in the db, and trigger a pre and post events for it
     * @param  mixed  $user
     * @param  string $provider
     * @param  mixed  $userProfile
     * @return mixed
     */
    protected function insert($user, $provider, $userProfile)
    {
        $lmcuserOptions = $this->getLmcUserOptions();

        // If user state is enabled, set the default state value
        if ($lmcuserOptions->getEnableUserState()) {
            if ($lmcuserOptions->getDefaultUserState()) {
                $user->setState((int) $lmcuserOptions->getDefaultUserState());
            }
        }

        $roleMapper          = $this->getRoleMapper();
        $defaultRegisterRole = $this->getOptions()->getDefaultRegisterRole();
        $role = $roleMapper->findByRoleId($defaultRegisterRole);
        $user->addRole($role);

        $options = array(
            'user'          => $user,
            'provider'      => $provider,
            'userProfile'   => $userProfile,
        );

        $this->getEventManager()->trigger('registerViaProvider', $this, $options);
        $result = $this->getLmcUserMapper()->insert($user);
        $this->getEventManager()->trigger('registerViaProvider.post', $this, $options);

        return $result;
    }

    /**
     * Set Event Manager
     *
     * @param  EventManagerInterface $events
     * @return HybridAuth
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;

        return $this;
    }

    /**
     * Get Event Manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(
                new EventManager(
                    $this->getServiceManager()->get('SharedEventManager'), [get_class($this)]
                )
            );
        }

        return $this->events;
    }

    /**
     * getRoleMapper
     *
     * @return UserMapperInterface
     */
    public function getRoleMapper()
    {
        if (null === $this->roleMapper) {
            $this->roleMapper = $this->getServiceManager()->get('playgrounduser_role_mapper');
        }

        return $this->roleMapper;
    }
}
