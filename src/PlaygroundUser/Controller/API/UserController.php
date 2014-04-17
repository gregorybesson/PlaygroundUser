<?php

namespace PlaygroundUser\Controller\API;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Crypt\Password\Bcrypt;
use Zend\Http\Response;
use PlaygroundUser\Entity\User;

class UserController extends AbstractActionController
{

   /**
    * POST 
    * identity => thomas.roger@adfab.fr
    * credential => troger
    * 
    */
    public function loginAction()
    {

        $form  = $this->getServiceLocator()->get('zfcuser_login_form');
        $form->setData($this->getRequest()->getPost());

        if (! $form->isValid()) {
            $return = array('status' => 1 , 'message' => 'user is not valid');

            return $this->sendResponse($return);

        }

        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();
        $result = $this->forward()->dispatch('playgrounduser_user', array(
            'action' => 'ajaxauthenticate'
        ));

        if (! $result) {
            $return = array('status' => 2 , 'message' => 'user is not valid');

            return $this->sendResponse($return);
        } 

        $user = $this->zfcUserAuthentication()->getIdentity();

        $token = $this->getServiceLocator()->get('playgrounduser_tokensecure_service')->generateValue($user->getId(), $user->getEmail());

        $user->setToken($token);
        $user = $this->getUserMapper()->update($user);

        $return = array('status' => 0, 'message' => '', 'token' => $token);
       
        return $this->sendResponse($return);
    }


    /**
    * POST
    * {"token":"916dc6827ee96b3210c0e36e53560763:34f91ab01416607c78eaf2acbd759276ebe41bad"}
    *
    */
    public function logoutAction()
    {

        $data = $this->getRequest()->getPost('data');

        $result = $this->checkUser($data);
        if(!$result instanceOf User) {
            return $result;
        }

        $user = $result;
            
        $user->setToken(null);
        $user = $this->getUserMapper()->update($user);
        

         $return = array('status' => 0 , 'message' => '');
        return $this->sendResponse($return);
    }
    
    /**
    * POST
    *
    * {"email":"thomas.roger@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr"}
    * {"email":"thomas.roger.facebook@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184"}
    * {"email":"thomas.roger.twitter@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "twitter" : "18410400"}
    * {"email":"thomas.roger.google@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "google" : "105792242901994540498"}
    * {"email":"thomas.roger.google+facebook@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184", "google" : "1232323"}
    *
    */
    public function createAction()
    {

        $zfcUserOptions = $this->getServiceLocator()->get('zfcuser_module_options');

        $data = $this->getRequest()->getPost('data');

        if(empty($data)){
            $return = array('status' => 3 , 'message' => 'data is required');

            return $this->sendResponse($return);
        }

        $data = json_decode($data, true);

        if (empty($data['email'])) {
            $return = array('status' => 4 , 'message' => 'user is not valid');

            return $this->sendResponse($return);
        }

        $users = $this->getUserMapper()->findByEmail($data['email']);
        if(count($users) > 0) {
            $return = array('status' => 5 , 'message' => 'user is already exist');

            return $this->sendResponse($return);
        }


        if (isset($data['dob']) && $data['dob']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['dob']);
            $data['dob'] = $tmpDate->format('Y-m-d');
        }

        if (empty($data['password'])) {
            $return = array('status' => 6 , 'message' => 'user is not valid');

            return $this->sendResponse($return);
        }

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($zfcUserOptions->getPasswordCost());
        $pass = $bcrypt->create($data['password']);
       
        $class = $zfcUserOptions->getUserEntityClass();
        $user  = new $class;
        $form  = $this->getServiceLocator()->get('zfcuser_register_form');
        $form->bind($user);

        $form->setData($data);

        if (!$form->isValid()) {
            $return = array('status' => 1 , 'message' => 'user is not valid');

            return $this->sendResponse($return);
        }

        if (!empty($data['address'])){
            $user->setAddress($data['address']);
        }
        if (!empty($data['city'])){
            $user->setCity($data['city']);
        }
        if (!empty($data['country'])){
            $user->setCountry($data['country']);
        }


        $roleMapper          = $this->getRoleMapper();
        $defaultRegisterRole = $this->getOptions()->getDefaultRegisterRole();
        if (isset($data['roleId'])) {
            $role = $roleMapper->findByRoleId($data['roleId']);
        } else {
            $role = $roleMapper->findByRoleId($defaultRegisterRole);
        }
        if ($role) $user->addRole($role);

        $user->setPassword($pass);
        $user->setState(1);        

        $user = $this->getUserMapper()->insert($user);

        $token = $this->getServiceLocator()->get('playgrounduser_tokensecure_service')->generateValue($user->getId(), $user->getEmail());

        $user->setToken($token);
        $user = $this->getUserMapper()->update($user);


        if(!empty($data['facebook'])){
            $this->addProviderToUser($user, $data['facebook'], 'facebook');
        }

        if(!empty($data['twitter'])){
            $this->addProviderToUser($user, $data['twitter'], 'twitter');
        }

        if(!empty($data['google'])){
            $this->addProviderToUser($user, $data['google'], 'google'); 
        }

        try {
            $this->sendNewEmailMessage($user->getEmail(), $data['password']);
        } catch(\Exception $e) {

        }

        $return = array('status' => 0, 'message' => '', 'token' => $token);

        return $this->sendResponse($return);
        
    }

    /**
    * POST
    * {"token":"916dc6827ee96b3210c0e36e53560763:34f91ab01416607c78eaf2acbd759276ebe41bad"}
    * {"token":"43fafd15a974783d8132322ff167f727:d64582eeaec9f2709396e0dd60d84922efd41a11"}
    *
    */
    public function profileAction()
    {
        $return = array();
        $profile = array();

        $data = $this->getRequest()->getPost('data');

        $result = $this->checkUser($data);
        if(!$result instanceOf User) {
            return $result;
        }

        $user = $result;

        $profile['email'] = $user->getEmail();
        $profile['firstname'] = $user->getFirstname();
        $profile['lastname'] = $user->getLastname();
        $profile['postalCode'] = $user->getPostalCode();
        $profile['dob'] = $user->getDob()->format('d/m/Y');
        $profile['address'] = $user->getAddress();
        $profile['city'] = $user->getCity();
        $profile['country'] = $user->getCountry();

        $providers = $this->getUserProviderMapper()->findProvidersByUser($user);
        foreach ($providers as $provider) {
            $profile[$provider->getProvider()] = $provider->getProviderId();
        }
            
        $return = array('status' => 0 , 'message' => '', 'profile' => $profile);
       
        return $this->sendResponse($return);
    }
    
    /**
    * POST
    *
    * {"token":"7cd23e13497e76a914b5c2528f1b3f7e:f29cb40b488ed4e7df6de44e567c50e371daab48","email":"thomas.roger.edit@adfab.fr","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr"}
    * {"token":"7cd23e13497e76a914b5c2528f1b3f7e:f29cb40b488ed4e7df6de44e567c50e371daab48","email":"thomas.roger.edit@adfab.fr","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "google" : "9999999"}
    * {"token":"c25df8574954fbd9bdfae13d6f38e2ad:2e36b897080780c8699d64046f442270018fbdf3","email":"thomas.roger.facebook.edit@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184999999", "google" : "000000011110000"}
    *  
    *
    */
    public function editAction()
    {
        $return = array();
        $profile = array();

        $data = $this->getRequest()->getPost('data');

        $result = $this->checkUser($data);
        if(!$result instanceOf User) {
            return $result;
        }

        $user = $result;
        $data = json_decode($data, true);

        if (empty($data['email'])) {
            $return = array('status' => 4 , 'message' => 'user is not valid');

            return $this->sendResponse($return);
        }

        $users = $this->getUserMapper()->findByEmail($data['email']);
        if(count($users) > 1) {
            $return = array('status' => 5 , 'message' => 'user is already exist');

            return $this->sendResponse($return);
        }

        $user->setEmail($data['email']);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setAddress($data['address']);
        $user->setPostalCode($data['postalCode']);
        $user->setCity($data['city']);
        $user->setCountry($data['country']);

        if (isset($data['dob']) && $data['dob']) {
            $data['dob'] = \DateTime::createFromFormat('d/m/Y', $data['dob']);
        }

        $user->setDob($data['dob']);

        $user = $this->getUserMapper()->update($user);
        $token = $this->getServiceLocator()->get('playgrounduser_tokensecure_service')->generateValue($user->getId(), $user->getEmail());

        $user->setToken($token);
        $user = $this->getUserMapper()->update($user);

        $this->removeProvidersToUser($user);

        if(!empty($data['facebook'])){
            $this->addProviderToUser($user, $data['facebook'], 'facebook');
        }

        if(!empty($data['twitter'])){
            $this->addProviderToUser($user, $data['twitter'], 'twitter');
        }

        if(!empty($data['google'])){
            $this->addProviderToUser($user, $data['google'], 'google'); 
        }

        
        $return = array('status' => 0, 'message' => '', 'token' => $token);


        return $this->sendResponse($return);
    }
    
    /**
    * POST
    * {"token":"916dc6827ee96b3210c0e36e53560763:34f91ab01416607c78eaf2acbd759276ebe41bad"}
    *
    */
    public function deleteAction()
    {

        $data = $this->getRequest()->getPost('data');

        
        $result = $this->checkUser($data);
        if(!$result instanceOf User) {
            return $result;
        }

        $user = $result;
        $user->setToken(null);
        $user->setState(2);
        $user = $this->getUserMapper()->update($user);
        

        $return = array('status' => 0 , 'message' => '');
       
        return $this->sendResponse($return);
    }


    public function checkUser($data)
    {
        if(empty($data)){
            $return = array('status' => 3, 'message' => 'data is required');

            return $this->sendResponse($return);
        }

        $data = json_decode($data, true);

        if(empty($data['token'])) {
            $return = array('status' => 8, 'message' => 'token is required');

            return $this->sendResponse($return);
        }

        $id = $this->getServiceLocator()->get('playgrounduser_tokensecure_service')->checkToken($data['token']);
        if ($id === false) {
            $return = array('status' => 9, 'message' => 'user not recognized');

            return $this->sendResponse($return);
        }

        $users = $this->getUserMapper()->findByToken($data['token']);
        if(empty($users)){
            $return = array('status' => 9, 'message' => 'user not recognized');

            return $this->sendResponse($return);
        }

        return $users[0];
    }

    public function sendResponse($return)
    {
        $response = $this->getResponse();
        $response->setStatusCode(200);

        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', '*');
        $response->setContent(json_encode($return));

        return $response;
    }

    public function sendNewEmailMessage($to, $password)
    {
        $mailService = $this->getServiceLocator()->get('playgrounduser_message');

        $from = $this->getOptions()->getEmailFromAddress();
        $subject = $this->getOptions()->getNewEmailSubjectLine();

        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-user/email/newemail', array('email' => $to, 'password' => $password));

        $mailService->send($message);
    }

    public function addProviderToUser($user, $providerId, $providerName)
    {
        $userProvider = new \PlaygroundUser\Entity\UserProvider();
        $userProvider->setProvider($providerName)
                ->setProviderId($providerId)
                ->setUser($user);

        return $this->getUserProviderMapper()->insert($userProvider);
    }


    public function removeProvidersToUser($user)
    {
        $userProviders = $this->getUserProviderMapper()->findProvidersByUser($user);

        foreach ($userProviders as $providerName => $userProvider) {
           $this->getUserProviderMapper()->remove($userProvider);
        }
    }

    public function getUserMapper()
    {
        return $this->getServiceLocator()->get('zfcuser_user_mapper');
    }

    public function getUserProviderMapper()
    {
        return $this->getServiceLocator()->get('playgrounduser_userprovider_mapper');
    }

    public function getRoleMapper()
    {
        return $this->getServiceLocator()->get('playgrounduser_role_mapper');
    }

    public function getOptions()
    {
        return $this->getServiceLocator()->get('playgrounduser_module_options');
    }
}