<?php

namespace PlaygroundUser\Service;

use PlaygroundUser\Entity\UserProvider;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Crypt\Password\Bcrypt;
use PlaygroundUser\Options\ModuleOptions;
use Zend\Validator\File\Size;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Session\Container;
use PlaygroundUser\Entity\User as UserEntity;

class User extends \ZfcUser\Service\User implements ServiceManagerAwareInterface
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

    /**
     * TODO : Refactor so that mandatory fields stay in the entity, and
     * functional mandatory fields go in the form validator part
     * @param  array           $data
     * @return boolean|unknown
     */
    public function create(array $data)
    {
    	$entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $class = $zfcUserOptions->getUserEntityClass();
        $user  = new $class;
        $form  = $this->getServiceManager()->get('playgrounduseradmin_register_form');
        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));

        $form->bind($user);

        $avatarPath = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
        $avatarUrl = $this->getOptions()->getAvatarUrl() . '/';

        $clearPassword = (isset($data['password']))?  $data['password'] : '';

        if ($this->getOptions()->getCreateUserAutoPassword()) {
            $rand = \Zend\Math\Rand::getString(8);
            $clearPassword = $rand;
            $bcrypt = new Bcrypt;
            $bcrypt->setCost($zfcUserOptions->getPasswordCost());
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
        $filter->get('title')->setRequired(FALSE);
        $filter->remove('firstname');
        $filter->remove('lastname');
        $filter->remove('postalCode');
        $filter->remove('dob');
        $form->setInputFilter($filter);

        $emailInput = $form->getInputFilter()->get('email');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
        		'object_repository' => $entityManager->getRepository($class),
        		'fields'            => 'email',
        		'messages'          => array('objectFound' => 'This email already exists !')
        ));

        $emailInput->getValidatorChain()->addValidator($noObjectExistsValidator);

        if (!$zfcUserOptions->getEnableUsername()) {
            unset($data['username']);
        }

        if (!$zfcUserOptions->getEnableDisplayName()) {
            unset($data['display_name']);
        }

        // If user state is enabled, set the default state value
        if ($zfcUserOptions->getEnableUserState()) {
            if ($zfcUserOptions->getDefaultUserState()) {
                /*$data = array_merge(
                    $data,
                    array('state'=> $zfcUserOptions->getDefaultUserState())
                );*/
                $user->setState((int) $zfcUserOptions->getDefaultUserState());
            }
        }

        // If avatar is set, I prepend the url path to the image
        $fileName=null;
        if (isset($data['avatar'])) {
            $fileName = $data['avatar'];
            $data['avatar'] = $avatarUrl . $fileName;
        }

        if (!isset($data['username']) || $data['username'] == '' ) {
            $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']),0,1);
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
        if ($role) $user->addRole($role);

        if ($fileName) {
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>'500kb'));
            $adapter->setValidators(array($size), $fileName);

            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
				if(isset($dataError['fileSizeTooBig'])) {
					$dataError['fileSizeTooBig'] = 'Votre fichier dépasse le poids autorisé';
				}
                $error = array();
                foreach ($dataError as $key=>$row) {
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
                    $this->sendNewEmailMessage($user->getEmail(),$clearPassword);

                    return $user;
                }
            }
        } else {
            $user = $this->getUserMapper()->insert($user);
            $this->sendNewEmailMessage($user->getEmail(),$clearPassword);

            return $user;
        }

        return false;
    }

    public function edit(array $data, $user)
    {
    	$entityManager 		  = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
    	$zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
    	$class 				  = $zfcUserOptions->getUserEntityClass();
        $path                 = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
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
        $filter->get('firstname')->setRequired(FALSE);
        $filter->get('lastname')->setRequired(FALSE);
        $form->setInputFilter($filter);

        $emailInput = $form->getInputFilter()->get('email');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
        		'object_repository' => $entityManager->getRepository($class),
        		'fields'            => 'email',
        		'messages'          => array('objectFound' => 'This email already exists !')
        ));

        if($user->getEmail() != $data['email']){
        	$emailInput->getValidatorChain()->addValidator($noObjectExistsValidator);
        }

        // If avatar is set, I prepend the url path to the image
        if (isset($data['avatar'])) {
            $fileName = $data['avatar'];
            $data['avatar'] = $avatar_url . $fileName;
        }

        if (!isset($data['username']) || $data['username'] == '' ) {
            $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']),0,1);
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
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>'500kb'));
            $adapter->setValidators(array($size), $fileName);
            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
				if(isset($dataError['fileSizeTooBig'])) {
					$dataError['fileSizeTooBig'] = 'Votre fichier dépasse le poids autorisé';
				}
                $error = array();
                foreach ($dataError as $key=>$row) {
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
     * @return \playgroundUser\Entity\UserInterface
     * @throws Exception\InvalidArgumentException
     */
    public function register(array $data)
    {
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $class = $zfcUserOptions->getUserEntityClass();
        $user  = new $class;
        $form  = $this->getRegisterForm();
        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));

		// Convert birth date format
		if (isset($data['dob']) && $data['dob']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['dob']);
            $data['dob'] = $tmpDate->format('Y-m-d');
        }

        $form->bind($user);
        $form->setData($data);
        // Fetch any valid object manager from the Service manager (here, an entity manager)
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        // Now get the input filter of the form, and add the validator to the email input
        $emailInput = $form->getInputFilter()->get('email');

        // This email is already associated with another user
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository($class),
            'fields'            => 'email',
            'messages'          => array('objectFound' => 'This email already exists !')
        ));

        $emailInput->getValidatorChain()->addValidator($noObjectExistsValidator);

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

        $user = $form->getData();
        /* @var $user \ZfcUser\Entity\UserInterface */

        // Si creation sociale, je ne crée pas le mot de passe
        if (!isset($data['socialNetwork'])) {
            $bcrypt = new Bcrypt;
            $bcrypt->setCost($zfcUserOptions->getPasswordCost());
            $user->setPassword($bcrypt->create($user->getPassword()));
        } else {
            $user->setPassword('auth_'.$data['socialNetwork']);
        }

        if ($zfcUserOptions->getEnableUsername()) {
            if (!isset($data['username']) || $data['username'] == '' ) {
                if(isset($data['firstname']) && isset($data['lastname'])){
                	$user->setUsername(ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']),0,1));
				}
            } else {
                $user->setUsername($data['username']);
            }
        }

        if ($zfcUserOptions->getEnableDisplayName()) {
            $user->setDisplayName($data['display_name']);
        }

        // If user state is enabled, set the default state value

        //set default state value for socialNetwork and others
        if ($zfcUserOptions->getEnableUserState()) {
            if ($zfcUserOptions->getDefaultUserState()) {
                $user->setState($zfcUserOptions->getDefaultUserState());
            }
        }

        /*
        if ($zfcUserOptions->getEnableUserState()) {
            if (!isset($data['socialNetwork'])) {
                if ($zfcUserOptions->getDefaultUserState()) {
                    $user->setState($zfcUserOptions->getDefaultUserState());
                }
            } else {
                // If socialNetwork, I must activate the user in this step
                if ($zfcUserOptions->getAllowedLoginStates() && is_array($zfcUserOptions->getAllowedLoginStates())) {
                    $states = $zfcUserOptions->getAllowedLoginStates();
                    $user->setState($states[0]);
                }
            }
        }
		*/

        $roleMapper  = $this->getRoleMapper();
        $defaultRegisterRole = $zfcUserOptions->getDefaultRegisterRole();
        $role        = $roleMapper->findByRoleId($defaultRegisterRole);
        if ($role) $user->addRole($role);

        $user = $this->getUserMapper()->insert($user);

        if (isset($data['socialNetwork']) && $user->getId()) {

            $userProvider = new \PlaygroundUser\Entity\UserProvider();
            $userProvider->setProvider($data['socialNetwork'])
                ->setProviderId($data['socialId'])
                ->setUser($user);

            $this->getProviderService()->getUserProviderMapper()->insert($userProvider);

        }
        //elseif ($this->getOptions()->getEmailVerification()) {
        if ($this->getOptions()->getEmailVerification()) {
            // If user verification by mail is enabled
            $this->cleanExpiredVerificationRequests();

            $verification = new \PlaygroundUser\Entity\EmailVerification();
            $verification->setEmailAddress($user->getEmail());
            $verification->generateRequestKey();
            $this->getEmailVerificationMapper()->insert($verification);

            $this->sendVerificationEmailMessage($verification);
        }
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));

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
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $rand = \Zend\Math\Rand::getString(8);
        $clearPassword = $rand;
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($zfcUserOptions->getPasswordCost());
        $pass = $bcrypt->create($rand);

        $user->setPassword($pass);

        $user = $this->getUserMapper()->update($user);
        $this->sendNewEmailMessage($user->getEmail(),$clearPassword);

        return true;
    }

    public function blockAccount(array $data)
    {
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $currentUser = $this->getAuthService()->getIdentity();

        $oldPass = $data['credential'];

        $activate = 1;
        if ($data['activate'] == 0) {
            $activate = 2;
        }

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($zfcUserOptions->getPasswordCost());

        if (!$bcrypt->verify($oldPass, $currentUser->getPassword())) {
            return false;
        }

        $currentUser->setState($activate);
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

    /**
     * Update user informations
     *
     * @param array                  $data
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
    public function updateInfo(array $data, $user)
    {
        $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));

        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');

        $form  = $this->getServiceManager()->get('playgrounduser_change_info_form');
        $form->get('dob')->setOptions(array('format' => 'Y-m-d'));
        $form->bind($user);

        $data['id'] = $user->getId();
        $avatarPath = $this->getOptions()->getAvatarPath() . DIRECTORY_SEPARATOR;
        $avatarUrl = $this->getOptions()->getAvatarUrl() . '/';

        // If avatar is set, I prepend the url path to the image
        $fileName=null;
        if (isset($data['avatar'])) {
            $fileName = $data['avatar'];
            $data['avatar'] = $avatarUrl . $fileName;
        }

        if (!isset($data['username']) || $data['username'] == '' ) {
            $data['username'] = ucfirst($data['firstname']) . " " . substr(ucfirst($data['lastname']),0,1);
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
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>'500kb'));
            $adapter->setValidators(array($size), $fileName);

            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
				if(isset($dataError['fileSizeTooBig'])) {
					$dataError['fileSizeTooBig'] = 'Votre fichier dépasse le poids autorisé';
				}
                $error = array();
                foreach ($dataError as $key=>$row) {
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

        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');

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
        $optinPartnerChange = false;

        // I trigger an optin event before updating user if it has changed .
        if($user->getOptin() != $data['optin']){
            $optinChange = true;
            $this->getEventManager()->trigger(__FUNCTION__.'.pre', $this, array('user' => $user, 'data' => $data));
        }

        // I trigger an optinPartner event before updating user if it has changed
        if($user->getOptinPartner() != $data['optinPartner']){
            $optinPartnerChange = true;
            $this->getEventManager()->trigger(__FUNCTION__.'Partner.pre', $this, array('user' => $user, 'data' => $data));
        }

        $form  = $this->getServiceManager()->get('playgrounduser_newsletter_form');
        $form->bind($user);
        $form->setData($data);

        //TODO : Check the form
        /*if (!$form->isValid()) {
            return false;
        }*/

        $user->setOptin($data['optin'])
            ->setOptinPartner($data['optinPartner']);

        $user = $this->getUserMapper()->update($user);
        if($optinChange){
            $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));
        }

        if($optinPartnerChange){
            $this->getEventManager()->trigger(__FUNCTION__.'Partner.post', $this, array('user' => $user, 'data' => $data));
        }

        return $user;
    }

    public function getQueryUsersByRole($role=1, $order=null, $search='')
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $filterSearch = '';

        if ($search != '') {
            $filterSearch = " AND (u.username like '%" . $search . "%' OR u.lastname like '%" . $search . "%' OR u.firstname like '%" . $search . "%' OR u.email like '%" . $search . "%')";
        }

        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();

        $query = $em->createQuery('
            SELECT u FROM ' . $userClass . ' u
            JOIN u.roles r
            WHERE r.id = :role' .
                $filterSearch .
                (in_array($order,array('ASC','DESC'))?' ORDER BY u.created_at '.$order:'').'
        ');
        $query->setParameter('role', $role);
        return $query;
    }

    public function getUsersByRole($role=1, $order='DESC', $search='')
    {
        $query = $this->getQueryUsersByRole();
        $result = $query->getResult();
        return $result;
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
        if(empty($user)) {
            // Pas d'utilisateur playground : alors on en crée un
            $user = new UserEntity();
            $user->setEmail($email);
            $rand = \Zend\Math\Rand::getString(8);
            $clearPassword = $rand;
            $bcrypt = new Bcrypt;
            $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
            $bcrypt->setCost($zfcUserOptions->getPasswordCost());
            $pass = $bcrypt->create($rand);
            $user->setPassword($pass);
            $user = $this->getUserMapper()->insert($user);
        }

        return $user;
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
            array('record' => $record, 'user' => $user, 'userFirstName' => $user->getFirstname())
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
            $this->userMapper = $this->getServiceManager()->get('zfcuser_user_mapper');
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
    public function setUserMapper(\ZfcUser\Mapper\UserInterface $userMapper)
    {
        $this->userMapper = $userMapper;

        return $this;
    }

    public function setOptions(\ZfcUser\Options\UserServiceOptionsInterface $options)
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

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $locator
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

}
