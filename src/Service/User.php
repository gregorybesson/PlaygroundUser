<?php

namespace PlaygroundUser\Service;

use PlaygroundUser\Entity\UserProvider;
use Laminas\Form\Form;
use Laminas\Stdlib\ErrorHandler;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Crypt\Password\Bcrypt;
use PlaygroundUser\Options\ModuleOptions;
use Laminas\Validator\File\Size;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Laminas\Session\Container;
use PlaygroundUser\Entity\User as UserEntity;
use PlaygroundUser\Entity\Role;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\EventManager\EventManager;

class User extends \LmcUser\Service\User
{

    /**
     * @var defaultRole
     */
    protected $defaultRole = 'user';

    /**
     * @var UserMapperInterface
     */
    protected $userMapper;

    /**
     * @var RoleMapperInterface
     */
    protected $roleMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Service Provider
     * @var
     */
    protected $providerService;

    protected $emailVerificationMapper;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    protected $event;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceManager = $locator;
    }

    /**
     * functional mandatory fields go in the form validator part
     * @param  array           $data
     * @return boolean|unknown
     */
    public function create(array $data)
    {
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $class = $lmcuserOptions->getUserEntityClass();
        $user  = new $class;
        $form  = $this->getServiceManager()->get('playgrounduseradmin_register_form');
        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));

        $form->bind($user);

        $avatarPath = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
        if (!is_dir($avatarPath)) {
            mkdir($avatarPath, 0777, true);
        }
        $avatarUrl = $this->getOptions()->getAvatarUrl() . '/';

        $clearPassword = (isset($data['password']))?  $data['password'] : '';

        if ($this->getOptions()->getCreateUserAutoPassword()) {
            $rand = \Laminas\Math\Rand::getString(8);
            $clearPassword = $rand;
            $bcrypt = new Bcrypt;
            $bcrypt->setCost($lmcuserOptions->getPasswordCost());
            $pass = $bcrypt->create($rand);
            /*$data = array_merge(
                $data,
                array('password'=> $pass)
            );*/
            $user->setPassword($pass);
        }

        $filter = $user->getInputFilter();
        $filter->remove('password');
        $filter->remove('passwordVerify');
        $filter->get('title')->setRequired(false);
        $filter->remove('firstname');
        $filter->remove('lastname');
        $filter->remove('postalCode');
        //$filter->remove('dob');
        $form->setInputFilter($filter);

        $emailInput = $form->getInputFilter()->get('email');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
                'object_repository' => $entityManager->getRepository($class),
                'fields'            => 'email',
                'messages'          => array('objectFound' => 'This email already exists !')
        ));

        $emailInput->getValidatorChain()->addValidator($noObjectExistsValidator);

        if (!$lmcuserOptions->getEnableUsername()) {
            unset($data['username']);
        }

        if (!$lmcuserOptions->getEnableDisplayName()) {
            unset($data['display_name']);
        }

        // If user state is enabled, set the default state value
        if ($lmcuserOptions->getEnableUserState()) {
            if ($lmcuserOptions->getDefaultUserState()) {
                /*$data = array_merge(
                    $data,
                    array('state'=> $lmcuserOptions->getDefaultUserState())
                );*/
                $user->setState((int) $lmcuserOptions->getDefaultUserState());
            }
        }

        // If avatar is set, I prepend the url path to the image
        $fileName=null;
        if (isset($data['avatar'])) {
            $fileName = $data['avatar'];
            $data['avatar'] = $avatarUrl . $fileName;
        }

        if (!isset($data['username']) || $data['username'] == '') {
            $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']), 0, 1);
        }

        // Convert birth date format
        if (isset($data['dob']) && $data['dob']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['dob']);
            $data['dob'] = $tmpDate->format('Y-m-d');
        }

        $form->setData($data);

        if (!$form->isValid()) {
            if (isset($data['dob']) && $data['dob']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                $data['dob'] = $tmpDate->format('d/m/Y');
                $form->setData(array('dob' => $data['dob']));
            }
            return false;
        }

        $roleMapper          = $this->getRoleMapper();
        $defaultRegisterRole = $this->getOptions()->getDefaultRegisterRole();
        if (isset($data['roleId'])) {
            $role = $roleMapper->findByRoleId($data['roleId']);
        } else {
            $role = $roleMapper->findByRoleId($defaultRegisterRole);
        }
        if ($role) {
            $user->addRole($role);
        }

        if ($fileName) {
            $adapter = new \Laminas\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>'500kb'));
            $adapter->setValidators(array($size), $fileName);

            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
                if (isset($dataError['fileSizeTooBig'])) {
                    $dataError['fileSizeTooBig'] = 'Votre fichier dépasse le poids autorisé';
                }
                $error = array();
                foreach ($dataError as $key => $row) {
                    $error[] = $row;
                }
                if (isset($data['dob']) && $data['dob']) {
                    $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                    $data['dob'] = $tmpDate->format('d/m/Y');
                    $form->setData(array('dob' => $data['dob']));
                }
                $form->setMessages(array('avatar'=>$error ));
            } else {
                $adapter->setDestination($avatarPath);
                if ($adapter->receive($fileName)) {
                    $user = $this->getUserMapper()->insert($user);
                    $this->sendNewEmailMessage($user->getEmail(), $clearPassword);

                    return $user;
                }
            }
        } else {
            $user = $this->getUserMapper()->insert($user);
            $this->sendNewEmailMessage($user->getEmail(), $clearPassword);

            return $user;
        }

        return false;
    }

    public function edit(array $data, $user)
    {
        $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));
        $entityManager          = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $class                  = $lmcuserOptions->getUserEntityClass();
        $path                 = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $avatar_url           = $this->getOptions()->getAvatarUrl() . '/';
        $roleMapper           = $this->getRoleMapper();
        $defaultRegisterRole  = $this->getOptions()->getDefaultRegisterRole();
        $form                 = $this->getServiceManager()->get('playgrounduseradmin_register_form');
        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));
        $fileName             = null;

        // remove password fields validation
        $filter = $user->getInputFilter();
        $filter->remove('password');
        $filter->remove('passwordVerify');
        $filter->get('firstname')->setRequired(false);
        $filter->get('lastname')->setRequired(false);
        $form->setInputFilter($filter);

        $emailInput = $form->getInputFilter()->get('email');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
                'object_repository' => $entityManager->getRepository($class),
                'fields'            => 'email',
                'messages'          => array('objectFound' => 'This email already exists !')
        ));

        if ($user->getEmail() != $data['email']) {
            $emailInput->getValidatorChain()->addValidator($noObjectExistsValidator);
        }

        // If avatar is set, I prepend the url path to the image
        if (isset($data['avatar'])) {
            $fileName = $data['avatar'];
            $data['avatar'] = $avatar_url . $fileName;
        }

        if (!isset($data['username']) || $data['username'] == '') {
            $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']), 0, 1);
        }

        // Convert birth date format
        if (isset($data['dob']) && $data['dob']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['dob']);
            $data['dob'] = $tmpDate->format('Y-m-d');
        }

        $form->bind($user);
        $form->setData($data);

        if (!$form->isValid()) {
            if (isset($data['dob']) && $data['dob']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                $data['dob'] = $tmpDate->format('d/m/Y');
                $form->setData(array('dob' => $data['dob']));
            }

            return false;
        }

        // updating roles in the entity
        $role = null;
        if (isset($data['roleId'])) {
            $role = $roleMapper->findByRoleId($data['roleId']);
        } elseif (count($user->getRoles()) == 0) {
            $role = $roleMapper->findByRoleId($defaultRegisterRole);
        }

        if ($fileName) {
            $adapter = new \Laminas\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>'500kb'));
            $adapter->setValidators(array($size), $fileName);
            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
                if (isset($dataError['fileSizeTooBig'])) {
                    $dataError['fileSizeTooBig'] = 'Votre fichier dépasse le poids autorisé';
                }
                $error = array();
                foreach ($dataError as $key => $row) {
                    $error[] = $row;
                }
                if (isset($data['dob']) && $data['dob']) {
                    $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                    $data['dob'] = $tmpDate->format('d/m/Y');
                    $form->setData(array('dob' => $data['dob']));
                }
                $form->setMessages(array('avatar'=>$error ));
            } else {
                $adapter->setDestination($path);
                if ($adapter->receive($fileName)) {
                    if ($role) {
                        $this->getUserMapper()->clearRoles($user);
                        $user->addRole($role);
                    }
                    $user = $this->getUserMapper()->update($user);
                    return $user;
                }
            }
        } else {
            if ($role) {
                $this->getUserMapper()->clearRoles($user);
                $user->addRole($role);
            }
            $user = $this->getUserMapper()->update($user);

            return $user;
        }
    }

    /**
     * Register the user (associated with a default). It can be a social registration
     *
     * @param  array $data
     * @param  string $formClass
     * @param  string $roleId
     * @return \playgroundUser\Entity\UserInterface
     * @throws Exception\InvalidArgumentException
     */
    public function register(array $data, $formClass = false, $roleId = null)
    {
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $class = $lmcuserOptions->getUserEntityClass();
        $user  = new $class;

        if ($formClass) {
            $form = $this->getServiceManager()->get($formClass);
        } else {
            $form  = $this->getRegisterForm();
        }

        $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data, 'form' => $form));

        $avatarPath = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
        if (!is_dir($avatarPath)) {
            mkdir($avatarPath, 0777, true);
        }
        $avatarUrl = $this->getOptions()->getAvatarUrl() . '/';

        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));

        // Convert birth date format
        if (isset($data['dob']) && $data['dob']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['dob']);
            $data['dob'] = $tmpDate->format('Y-m-d');
        }

        if ($lmcuserOptions->getEnableUsername()) {
            if (!isset($data['username']) || $data['username'] == '') {
                if (isset($data['firstname']) && isset($data['lastname'])) {
                    $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']), 0, 1);
                }
            }
        }

        $form->bind($user);
        $form->setData($data);

        // Now get the input filter of the form, and add the validator to the email input
        $emailInput = $form->getInputFilter()->get('email');

        // This email is already associated with another user
        $noObjectExistsValidator = new NoObjectExistsValidator(
            [
                'object_repository' => $entityManager->getRepository($class),
                'fields'            => 'email',
                'messages'          => array('objectFound' => 'This email already exists !')
            ]
        );

        $emailInput->getValidatorChain()->addValidator($noObjectExistsValidator);

        if ($lmcuserOptions->getEnableUsername() && $this->getOptions()->getUsernameUnique()) {
            $usernameInput = $form->getInputFilter()->get('username');
            // This username is already associated with another user
            $noObjectExistsValidator = new NoObjectExistsValidator(
                [
                    'object_repository' => $entityManager->getRepository($class),
                    'fields'            => 'username',
                    'messages'          => array('objectFound' => 'This username already exists !')
                ]
            );

            $usernameInput->getValidatorChain()->addValidator($noObjectExistsValidator);
        }

        // $filter = $user->getInputFilter();
        // $filter->remove('password');
        // $filter->remove('passwordVerify');
        // $form->setInputFilter($filter);

        if (!$form->isValid()) {
            if (isset($data['dob']) && $data['dob']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                $data['dob'] = $tmpDate->format('d/m/Y');
                $form->setData(array('dob' => $data['dob']));
            }
            return false;
        }

        $user = $form->getData();
        /* @var $user \LmcUser\Entity\UserInterface */

        // Si creation sociale, je ne crée pas le mot de passe
        if (!isset($data['socialNetwork'])) {
            $bcrypt = new Bcrypt;
            $bcrypt->setCost($lmcuserOptions->getPasswordCost());
            $user->setPassword($bcrypt->create($user->getPassword()));
        } else {
            $user->setPassword('auth_'.$data['socialNetwork']);
        }

        if ($lmcuserOptions->getEnableUsername()) {
            $user->setUsername($data['username']);
        }

        if ($lmcuserOptions->getEnableDisplayName()) {
            $user->setDisplayName($data['display_name']);
        }

        // If user state is enabled, set the default state value

        //set default state value for socialNetwork and others
        if ($lmcuserOptions->getEnableUserState()) {
            if ($lmcuserOptions->getDefaultUserState()) {
                $user->setState($lmcuserOptions->getDefaultUserState());
            }
        }

        /*
        if ($lmcuserOptions->getEnableUserState()) {
            if (!isset($data['socialNetwork'])) {
                if ($lmcuserOptions->getDefaultUserState()) {
                    $user->setState($lmcuserOptions->getDefaultUserState());
                }
            } else {
                // If socialNetwork, I must activate the user in this step
                if ($lmcuserOptions->getAllowedLoginStates() && is_array($lmcuserOptions->getAllowedLoginStates())) {
                    $states = $lmcuserOptions->getAllowedLoginStates();
                    $user->setState($states[0]);
                }
            }
        }
        */

        $roleMapper  = $this->getRoleMapper();
        if ($roleId) {
            $role = $roleMapper->findByRoleId($roleId);
            if ($role) {
                $user->addRole($role);
            } else {
                $roleId = null;
            }
        }
        if ($roleId == null) {
            $defaultRegisterRole = $lmcuserOptions->getDefaultRegisterRole();
            $role = $roleMapper->findByRoleId($defaultRegisterRole);
            if ($role) {
                $user->addRole($role);
            }
        }

        $user = $this->getUserMapper()->insert($user);

        if (! empty($data['avatar']['tmp_name'])) {
            ErrorHandler::start();
            $data['avatar']['name'] = $user->getId() . "-" . $data['avatar']['name'];
            rename($data['avatar']['tmp_name'], $avatarPath . $data['avatar']['name']);
            $user->setAvatar($avatarUrl . $data['avatar']['name']);
            $user = $this->getUserMapper()->update($user);
            ErrorHandler::stop(true);
        }

        if (isset($data['socialNetwork']) && $user->getId()) {
            $userProvider = new \PlaygroundUser\Entity\UserProvider();
            $userProvider->setProvider($data['socialNetwork'])
                ->setProviderId($data['socialId'])
                ->setUser($user);

            $this->getProviderService()->getUserProviderMapper()->insert($userProvider);
        }
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));
        
        if ($this->getOptions()->getEmailVerification()) {
            // If user verification by mail is enabled
            $this->cleanExpiredVerificationRequests();

            $verification = new \PlaygroundUser\Entity\EmailVerification();
            $verification->setEmailAddress($user->getEmail());
            $verification->generateRequestKey();
            $this->getEmailVerificationMapper()->insert($verification);

            $this->sendVerificationEmailMessage($verification);
        } elseif ($this->getOptions()->getEmailConfirmation()) {
            $this->sendConfirmationEmailMessage($user);
        }

        // Is there a sponsor on this registration ?
        $session = new Container('sponsorship');
        // Is there a secretKey in session ?
        if ($session->offsetGet('key')) {
            $this->getEventManager()->trigger('sponsor.post', $this, array('user' => $user, 'data' => $data, 'secretKey' => $session->offsetGet('key')));
        }

        return $user;
    }

    public function resetPassword($user)
    {
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $rand = \Laminas\Math\Rand::getString(8);
        $clearPassword = $rand;
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($lmcuserOptions->getPasswordCost());
        $pass = $bcrypt->create($rand);

        $user->setPassword($pass);

        $user = $this->getUserMapper()->update($user);
        $this->sendNewEmailMessage($user->getEmail(), $clearPassword);

        return true;
    }

    public function blockAccount(array $data)
    {
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $currentUser = $this->getAuthService()->getIdentity();

        $oldPass = $data['credential'];

        $activate = 1;
        if ($data['activate'] == 0) {
            $activate = 2;
        }

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($lmcuserOptions->getPasswordCost());

        if (!$bcrypt->verify($oldPass, $currentUser->getPassword())) {
            return false;
        }

        $currentUser->setState($activate);
        $currentUser->setEmail('INACTIVE---'.$currentUser->getEmail());
        $currentUser->setPassword('INACTIVE---'.$currentUser->getPassword());
        $this->getUserMapper()->update($currentUser);

        return true;
    }

    public function sendNewEmailMessage($to, $password)
    {
        $mailService = $this->getServiceManager()->get('playgrounduser_message');

        $from = $this->getOptions()->getEmailFromAddress();
        $subject = $this->getOptions()->getNewEmailSubjectLine();

        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-user/email/newemail', array('email' => $to, 'password' => $password));

        $mailService->send($message);
    }

    public function autoCompleteUser($field, $value)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        // I Have to know what is the User Class used
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $userClass = $lmcuserOptions->getUserEntityClass();

        $qb = $em->createQueryBuilder();
        $qb->select('u.id, u.'.$field)
            ->from($userClass, 'u')
            ->where($qb->expr()->like('u.'.$field, ':value'))
            ->orderBy('u.'.$field, 'ASC')
            ->setParameter('value', '%'.$value.'%');

        $query = $qb->getQuery();
        $array = $query->getArrayResult();

        return $array;
    }

    /**
     * Update user informations
     *
     * @param array                  $data
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
    public function updateInfo(array $data, $user)
    {
        $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));

        $form  = $this->getServiceManager()->get('playgrounduser_change_info_form');
        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));
        $form->bind($user);

        $data['id'] = $user->getId();
        $avatarPath = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
        if (!is_dir($avatarPath)) {
            mkdir($avatarPath, 0777, true);
        }
        $avatarUrl = $this->getOptions()->getAvatarUrl() . '/';

        // If avatar is set, I prepend the url path to the image
        $fileName = null;
        if (isset($data['avatar'])) {
            $fileName = $data['avatar'];
            $data['avatar'] = $avatarUrl . $fileName;
        }

        if (!isset($data['username']) || $data['username'] == '') {
            if(isset($data['firstname']) && isset($data['lastname'])) {
                $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']), 0, 1);
            }
        }

        // Convert birth date format
        if (isset($data['dob']) && $data['dob']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['dob']);
            $data['dob'] = $tmpDate->format('Y-m-d');
        }

        $form->setData($data);

        $filter = $user->getInputFilter();
        $filter->remove('password');
        $filter->remove('passwordVerify');
        $form->setInputFilter($filter);

        if (!$form->isValid()) {
            if (isset($data['dob']) && $data['dob']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                $data['dob'] = $tmpDate->format('d/m/Y');
                $form->setData(array('dob' => $data['dob']));
            }

            return false;
        }

        if ($fileName) {
            $adapter = new \Laminas\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>'500kb'));
            $adapter->setValidators(array($size), $fileName);

            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
                if (isset($dataError['fileSizeTooBig'])) {
                    $dataError['fileSizeTooBig'] = 'Votre fichier dépasse le poids autorisé';
                }
                $error = array();
                foreach ($dataError as $key => $row) {
                    $error[] = $row;
                }
                if (isset($data['dob']) && $data['dob']) {
                    $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                    $data['dob'] = $tmpDate->format('d/m/Y');
                    $form->setData(array('dob' => $data['dob']));
                }
                $form->setMessages(array('avatar'=>$error ));
            } else {
                $adapter->setDestination($avatarPath);
                if ($adapter->receive()) {
                    $user->setAvatar($avatarUrl . $adapter->getFileName(null, false));
                    $user = $this->getUserMapper()->update($user);
                    $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));

                    return $user;
                }
            }
        } else {
            $user = $this->getUserMapper()->update($user);
            $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));

            return $user;
        }

        return false;
    }

    /**
     * Update user address informations
     *
     * @param array                  $data
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
    public function updateAddress(array $data, $user)
    {
        $this->getEventManager()->trigger('updateInfo.pre', $this, array('user' => $user, 'data' => $data));

        $form  = $this->getServiceManager()->get('playgrounduser_address_form');
        $form->bind($user);
        $form->setData($data);

        $filter = $user->getInputFilter();
        $filter->remove('password');
        $filter->remove('passwordVerify');
        $filter->remove('dob');

        $form->setInputFilter($filter);

        if ($form->isValid()) {
            $user = $this->getUserMapper()->update($user);
            $this->getEventManager()->trigger('updateInfo.post', $this, array('user' => $user, 'data' => $data));

            if ($user) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Newsletter optins are updated
     *
     * @param array $data
     */
    public function updateNewsletter(array $data)
    {
        $user = $this->getAuthService()->getIdentity();
        $optinChange = false;
        $optin2Change = false;
        $optin3Change = false;
        $optinPartnerChange = false;

        // I trigger an optin event before updating user if it has changed .
        if (isset($data['optin']) && $user->getOptin() != $data['optin']) {
            $optinChange = true;
            $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));
            $user->setOptin($data['optin']);
        }

        // I trigger an optin2 event before updating user if it has changed .
        if (isset($data['optin2']) && $user->getOptin2() != $data['optin2']) {
            $optin2Change = true;
            $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));
            $user->setOptin2($data['optin2']);
        }

        // I trigger an optin3 event before updating user if it has changed .
        if (isset($data['optin3']) && $user->getOptin3() != $data['optin3']) {
            $optin3Change = true;
            $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));
            $user->setOptin3($data['optin3']);
        }

        // I trigger an optinPartner event before updating user if it has changed
        if (isset($data['optinPartner']) && $user->getOptinPartner() != $data['optinPartner']) {
            $optinPartnerChange = true;
            $this->getEventManager()->trigger(__FUNCTION__.'Partner.pre', $this, array('user' => $user, 'data' => $data));
            $user->setOptinPartner($data['optinPartner']);
        }

        $form  = $this->getServiceManager()->get('playgrounduser_newsletter_form');
        $form->bind($user);
        $form->setData($data);

        /*if (!$form->isValid()) {
            return false;
        }*/

        $user = $this->getUserMapper()->update($user);
        if ($optinChange) {
            $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));
        }

        if ($optin2Change) {
            $this->getEventManager()->trigger(__FUNCTION__.'.optin2.post', $this, array('user' => $user, 'data' => $data));
        }

        if ($optin3Change) {
            $this->getEventManager()->trigger(__FUNCTION__.'.optin3.post', $this, array('user' => $user, 'data' => $data));
        }

        if ($optinPartnerChange) {
            $this->getEventManager()->trigger(__FUNCTION__.'Partner.post', $this, array('user' => $user, 'data' => $data));
        }

        return $user;
    }

    /**
     * @param  array           $data
     * @return boolean|unknown
     */
    public function createRole(array $data)
    {
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $role  = new Role;
        $form  = $this->getServiceManager()->get('playgrounduseradmin_role_form');

        $form->bind($role);

        $roleId = $form->getInputFilter()->get('roleId');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
                'object_repository' => $entityManager->getRepository('\PlaygroundUser\Entity\Role'),
                'fields'            => 'roleId',
                'messages'          => array('objectFound' => 'This role already exists !')
        ));

        $roleId->getValidatorChain()->addValidator($noObjectExistsValidator);

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $roleMapper          = $this->getRoleMapper();
        $defaultRegisterRole = $this->getOptions()->getDefaultRegisterRole();
        if (isset($data['parentRoleId'])) {
            $parentRole = $roleMapper->findByRoleId($data['parentRoleId']);
        } else {
            $parentRole = $roleMapper->findByRoleId($defaultRegisterRole);
        }
        if ($parentRole) {
            $role->setParent($parentRole);
        }

        $role = $roleMapper->insert($role);

        return $role;
    }

    public function getQueryUsersByRole($role = null, $order = null, $search = '', $filterField = null, $filterValue = null)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $order = (in_array($order, array('ASC', 'DESC')))?$order:'DESC';

        // I Have to know what is the User Class used
        $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
        $userClass = $lmcuserOptions->getUserEntityClass();

        $qb = $em->createQueryBuilder();
        $qb->select('u')
            ->from($userClass, 'u')
            ->leftJoin('u.roles', 'r')
            ->leftJoin('u.teams', 't');
        //$qb->setParameter('userClass', $userClass);

        $and = $qb->expr()->andx();
        $and->add($qb->expr()->eq(1, 1));

        if ($role) {
            $and->add($qb->expr()->eq('r.id', ':roleId'));
            if (is_integer($role)) {
                $qb->setParameter('roleId', $role);
            } else {
                $qb->setParameter('roleId', $role->getId());
            }
        }
        
        if ($search != '') {
            $and->add(
                $qb->expr()->orX(
                    $qb->expr()->like('u.username', ':search1'),
                    $qb->expr()->like('u.firstname', ':search2'),
                    $qb->expr()->like('u.lastname', ':search3'),
                    $qb->expr()->like('u.email', ':search4'),
                    $qb->expr()->like('r.roleId', ':search5'),
                    $qb->expr()->like('t.name', ':search6')
                )
            );
            $qb->setParameter('search1', '%'.$search.'%');
            $qb->setParameter('search2', '%'.$search.'%');
            $qb->setParameter('search3', '%'.$search.'%');
            $qb->setParameter('search4', '%'.$search.'%');
            $qb->setParameter('search5', '%'.$search.'%');
            $qb->setParameter('search6', '%'.$search.'%');
        }

        if ($filterField && $filterValue) {
            $and->add(
                $qb->expr()->eq('u.'.$filterField, ':filter')
            );
            $qb->setParameter('filter', $filterValue);
        }

        $qb->where($and);
        $qb->orderBy('u.created_at', $order);

        return $qb;
    }

    public function getArrayUsersByRole($role = null, $order = null, $search = '')
    {
        $qb = $this->getQueryUsersByRole($role, $order, $search);
        $users = array(
            array(
                'id' => 1,
                'username' => 1,
                'title' => 1,
                'firstname' => 1,
                'lastname' => 1,
                'email' => 1,
                'optin' => 1,
                'optinPartner' => 1,
                'address' => 1,
                'address2' => 1,
                'postalCode' => 1,
                'city' => 1,
                'telephone' => 1,
                'mobile' => 1,
                'created_at' => 1,
                'dob' => 1,
                'role' => 1 ,
                'team' => 1
            )
        );
        foreach ($qb->getQuery()->getResult() as $user) {
            $a = array();
            $a[] = $user->getId();
            $a[] = $user->getUsername();
            $a[] = $user->getTitle();
            $a[] = $user->getFirstname();
            $a[] = $user->getLastname();
            $a[] = $user->getEmail();
            $a[] = $user->getOptin();
            $a[] = $user->getOptinPartner();
            $a[] = $user->getAddress();
            $a[] = $user->getAddress2();
            $a[] = $user->getPostalCode();
            $a[] = $user->getCity();
            $a[] = $user->getTelephone();
            $a[] = $user->getMobile();
            $a[] = $user->getCreatedAt()->format('d/m/Y');
            $a[] = (!empty($user->getDob()))?$user->getDob()->format('d/m/Y'):'';
            $roles='';
            $cr=count($user->getRoles());
            $i=0;
            foreach ($user->getRoles() as $r) {
                $roles .= $r->getRoleId();
                if ($i<$cr-1) {
                    $roles .= '|';
                }
                $i++;
            }
            $a[] = $roles;
            $teams='';
            $ct=count($user->getTeams());
            $i=0;
            foreach ($user->getTeams() as $t) {
                $teams .= $t->getName();
                if ($i<$ct-1) {
                    $teams .= '|';
                }
                $i++;
            }
            $a[] = $teams;

            $users[] = $a;
        }

        return $users;
    }

    public function getUsersByRole($role = 1, $order = 'DESC', $search = '')
    {
        $qb = $this->getQueryUsersByRole($role, $order, $search);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getUserActions()
    {
        $config     = $this->serviceManager->get('config');
        $logFrontendUser = ($config['playgrounduser']['log_frontend_user']) ? $config['playgrounduser']['log_frontend_user'] : false;
        $routes = [];

        if ($logFrontendUser) {
            $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
            $emConfig = $em->getConfiguration();
            $emConfig->addCustomStringFunction('SUBSTRING_INDEX', '\DoctrineExtensions\Query\Mysql\SubstringIndex');
            $query = $em->createQuery(
                "select DISTINCT CONCAT(SUBSTRING_INDEX(ul.controllerClass, '\\', -1), '/', ul.actionName) as path from PlaygroundUser\Entity\UserLog ul
                WHERE SUBSTRING_INDEX(ul.uri, '.', -1) NOT IN ('jpg','svg','gif','png','css','js')
                AND ul.areaName = 'frontend'"
            );
            $routes = $query->getResult();
        }

        return $routes;
    }

    /**
    * findUserOrCreateByEmail : retrieve user with email or create user if not exist
    * @param string $email
    *
    * @return User $user
    */
    public function findUserOrCreateByEmail($email)
    {
        $user = $this->getUserMapper()->findByEmail($email);
        if (empty($user)) {
            // Pas d'utilisateur playground : alors on en crée un
            $user = new UserEntity();
            $user->setEmail($email);
            $rand = \Laminas\Math\Rand::getString(8);
            $bcrypt = new Bcrypt;
            $lmcuserOptions = $this->getServiceManager()->get('lmcuser_module_options');
            $bcrypt->setCost($lmcuserOptions->getPasswordCost());
            $pass = $bcrypt->create($rand);
            $user->setPassword($pass);
            $user = $this->getUserMapper()->insert($user);
        }

        return $user;
    }

    /**
     *  getCSV creates lines of CSV and returns it.
     */
    public function getCSV($array)
    {
        ob_start(); // buffer the output ...
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($array[0]), ";");
        array_shift($array);
        foreach ($array as $line) {
            fputcsv($out, $line, ";");
        }
        fclose($out);
        return ob_get_clean(); // ... then return it as a string!
    }

    public function findAll()
    {
        return $this->getUserMapper()->findAll();
    }

    public function findByRequestKey($token)
    {
        return $this->getEmailVerificationMapper()->findByRequestKey($token);
    }

    public function findByEmail($email)
    {
        return $this->getEmailVerificationMapper()->findByEmail($email);
    }

    public function findByState($state)
    {
        return $this->getUserMapper()->findByState($state);
    }

    public function findByTitle($title)
    {
        return $this->getUserMapper()->findByTitle($title);
    }

    public function findByOptin($optin, $partner = false)
    {
        return $this->getUserMapper()->findByOptin($optin, $partner);
    }

    public function cleanExpiredVerificationRequests()
    {
        return $this->getEmailVerificationMapper()->cleanExpiredVerificationRequests();
    }

    public function remove(\PlaygroundUser\Entity\EmailVerification $m)
    {
        return $this->getEmailVerificationMapper()->remove($m);
    }

    public function sendVerificationEmailMessage(\PlaygroundUser\Entity\EmailVerification $record)
    {
        $mailService = $this->getServiceManager()->get('playgrounduser_message');

        $user = $this->getUserMapper()->findByEmail($record->getEmailAddress());

        $from = $this->getOptions()->getEmailFromAddress();
        //$subject = $this->getOptions()->getRegisterEmailSubjectLine();
        $subject = 'Terminez votre inscription !';

        $message = $mailService->createHtmlMessage(
            $from,
            $record->getEmailAddress(),
            $subject,
            'playground-user/email/verification',
            array('record' => $record, 'user' => $user)
        );

        $mailService->send($message);
    }

    public function sendConfirmationEmailMessage($user)
    {
        $mailService = $this->getServiceManager()->get('playgrounduser_message');

        $from = $this->getOptions()->getEmailFromAddress();
        
        $subject = 'Merci pour votre inscription';

        $message = $mailService->createHtmlMessage(
            $from,
            $user->getEmail(),
            $subject,
            'playground-user/email/confirmation',
            array('user' => $user)
        );

        $mailService->send($message);
    }

    public function getEmailVerificationMapper()
    {
        if (null === $this->emailVerificationMapper) {
            $this->emailVerificationMapper = $this->getServiceManager()->get('playgrounduser_emailverification_mapper');
        }

        return $this->emailVerificationMapper;
    }

    /**
     * setEmailVerificationMapper
     *
     * @param EmailVerification $mapper
     * @return
     */
    public function setEmailVerificationMapper($mapper)
    {
        $this->emailVerificationMapper = $mapper;

        return $this;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('lmcuser_user_mapper');
        }

        return $this->userMapper;
    }

    /**
     * getUserMapper
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

    /**
     * setUserMapper
     *
     * @param  UserMapperInterface $userMapper
     * @return User
     */
    public function setUserMapper(\LmcUser\Mapper\UserInterface $userMapper)
    {
        $this->userMapper = $userMapper;

        return $this;
    }

    public function setOptions(\LmcUser\Options\UserServiceOptionsInterface $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }

    /**
     * initialisation du service
     * @param  $service
     */
    public function setProviderService($service)
    {
        $this->providerService = $service;
    }

    /**
     * retourne le service provider
     * @return
     */
    public function getProviderService()
    {
        if ($this->providerService == null) {
            $this->setProviderService($this->getServiceManager()->get('playgrounduser_provider_service'));
        }

        return $this->providerService;
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

    public function setServiceManager(\Interop\Container\ContainerInterface $container)	
    {	
        $this->serviceManager = $container;	

        return $this;	
    }

    public function getEventManager()
    {
        if ($this->event === NULL) {
            $this->event = new EventManager(
                $this->getServiceManager()->get('SharedEventManager'), [get_class($this)]
            );
        }
        return $this->event;
    }
}
