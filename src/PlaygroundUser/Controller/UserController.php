<?php
namespace PlaygroundUser\Controller;

use Hybrid_Auth;
use Zend\Form\Form;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use ZfcUser\Controller\UserController as ZfcUserController;
use Zend\View\Model\ViewModel;

class UserController extends ZfcUserController
{
    const ROUTE_CHANGEPASSWD = 'frontend/zfcuser/changepassword';
    // No login page but register page !
    const ROUTE_LOGIN        = 'frontend/zfcuser/register';
    const ROUTE_REGISTER     = 'frontend/zfcuser/register';
    const ROUTE_CHANGEEMAIL  = 'frontend/zfcuser/changeemail';

    /**
     *
     * @var Form
     */
    protected $changeInfoForm;

    protected $blockAccountForm;

    protected $newsletterForm;

    protected $addressForm;

    protected $prizeCategoryForm;

    protected $coreOptions;
    /**
     * @var Hybrid_Auth
     */
    protected $hybridAuth;

    /**
     * @param callable $redirectCallback
     */
    public function __construct($redirectCallback)
    {
        parent::__construct($redirectCallback);
    }

    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if (!$request->isPost()) {
            // je redirige vers inscription
            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_REGISTER).($redirect ? '?redirect='.$redirect : ''));
        }

        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_REGISTER).($redirect ? '?redirect='.$redirect : ''));
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->forward()->dispatch('playgrounduser_user', array('action' => 'authenticate'));
    }

    /**
     * Register new user
     */
    public function registerAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toUrl($this->url()->fromRoute($this->getOptions()->getLoginRedirectRoute()));
        }
        $request = $this->getRequest();
        $service = $this->getUserService();
        $form = $this->getRegisterForm();
        $socialnetwork = $this->params()->fromRoute('socialnetwork', false);
        $form->setAttribute('action', $this->url()->fromRoute('frontend/zfcuser/register'));
        $params = array();
        $socialCredentials = array();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if ($socialnetwork) {
            $infoMe = null;
            $infoMe = $this->getProviderService()->getInfoMe($socialnetwork);

            if (!empty($infoMe)) {
                $user = $this->getProviderService()->getUserProviderMapper()->findUserByProviderId($infoMe->identifier, $socialnetwork);

                if ($user || $service->getOptions()->getCreateUserAutoSocial() == true) {
                    //on le dirige vers l'action d'authentification
                    if (! $redirect && $this->getOptions()->getLoginRedirectRoute() != '') {
                        $redirect = $this->url()->fromRoute($this->getOptions()->getLoginRedirectRoute());
                    }
                    $redir = $this->url()
                        ->fromRoute('frontend/zfcuser/login') .'/' . $socialnetwork . ($redirect ? '?redirect=' . $redirect : '');

                    return $this->redirect()->toUrl($redir);
                }

                // Je retire la saisie du login/mdp
                $form->setAttribute('action', $this->url()->fromRoute('frontend/zfcuser/register', array('socialnetwork' => $socialnetwork)));
                $form->remove('password');
                $form->remove('passwordVerify');

                $birthMonth = $infoMe->birthMonth;
                if (strlen($birthMonth) <= 1) {
                    $birthMonth = '0'.$birthMonth;
                }
                $birthDay = $infoMe->birthDay;
                if (strlen($birthDay) <= 1) {
                    $birthDay = '0'.$birthDay;
                }
                $title = '';
                $gender = $infoMe->gender;
                if ($gender == 'female') {
                    $title = 'Me';
                } else {
                    $title = 'M';
                }

                $params = array(
                    //'birth_year'  => $infoMe->birthYear,
                    'title'      => $title,
                    'dob'      => $birthDay.'/'.$birthMonth.'/'.$infoMe->birthYear,
                    'firstname'   => $infoMe->firstName,
                    'lastname'    => $infoMe->lastName,
                    'email'       => $infoMe->email,
                    'postalCode' => $infoMe->zip,
                );
                $socialCredentials = array(
                    'socialNetwork' => strtolower($socialnetwork),
                    'socialId'      => $infoMe->identifier,
                );
            }
        }

        $redirectUrl = $this->url()->fromRoute('frontend/zfcuser/register') .($socialnetwork ? '/' . $socialnetwork : ''). ($redirect ? '?redirect=' . $redirect : '');
        $prg = $this->prg($redirectUrl, true);

        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $form->setData($params);

            return array(
                'registerForm' => $form,
                'enableRegistration' => $this->getOptions()->getEnableRegistration(),
                'redirect' => $redirect
            );
        }

        $post = $prg;
        $post = array_merge(
            $post,
            $socialCredentials
        );

        $user = $service->register($post);

        if (! $user) {
            return array(
                'registerForm' => $form,
                'enableRegistration' => $this->getOptions()->getEnableRegistration(),
                'redirect' => $redirect
            );
        }

        //if (!$socialnetwork) {

        if ($service->getOptions()->getEmailVerification()) {
            $vm = new ViewModel(array('userEmail' => $user->getEmail()));
            $vm->setTemplate('playground-user/register/registermail');

            return $vm;
        } elseif ($service->getOptions()->getLoginAfterRegistration()) {
            $identityFields = $service->getOptions()->getAuthIdentityFields();
            if (in_array('email', $identityFields)) {
                $post['identity'] = $user->getEmail();
            } elseif (in_array('username', $identityFields)) {
                $post['identity'] = $user->getUsername();
            }
            $post['credential'] = isset($post['password'])?$post['password']:'';
            $request->setPost(new Parameters($post));

            return $this->forward()->dispatch('playgrounduser_user', array(
                'action' => 'authenticate'
            ));
        }
        //}

        // TODO: Add the redirect parameter here...
        $redirect = $this->url()->fromRoute('frontend/zfcuser/login') . ($socialnetwork ? '/' . $socialnetwork : ''). ($redirect ? '?redirect=' . $redirect : '');

        return $this->redirect()->toUrl($redirect);
    }

    /**
     * Backend D'HybridAuth utilisé pour l'authentification
     */
    public function HybridAuthBackendAction()
    {
        try {
            \Hybrid_Endpoint::process();
        } catch (\Exception $e) {
            return $this->redirect()->toUrl(
                $this->url()->fromRoute('frontend')
            );
        }
    }

    public function ajaxloginAction()
    {
        $form = $this->getLoginForm();
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getPost()->get('redirect')) {
            $redirect = $request->getPost()->get('redirect');
        } else {
            $redirect = false;
        }

        $messages = array();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                $errors = $form->getMessages();
                /*
                 * foreach ($errors as $key=>$row) { if (!empty($row) && $key !=
                 * 'submit') { foreach ($row as $keyer => $rower) {
                 * $messages[$keyer] = $rower; } } }
                 */
            }

            if (! empty($messages)) {
                $response->setContent(\Zend\Json\Json::encode(array(
                    'success' => 0
                )));
            } else {
                $this->zfcUserAuthentication()
                    ->getAuthAdapter()
                    ->resetAdapters();
                $this->zfcUserAuthentication()
                    ->getAuthService()
                    ->clearIdentity();
                $result = $this->forward()->dispatch('playgrounduser_user', array(
                    'action' => 'ajaxauthenticate'
                ));
                if (! $result) {
                    $response->setContent(\Zend\Json\Json::encode(array(
                        'success' => 0
                    )));
                } else {
                    $response->setContent(\Zend\Json\Json::encode(array(
                        'success' => 1
                    )));
                }
            }
        }

        return $response;
    }

    /**
     * Ajax authentication action
     */
    public function ajaxauthenticateAction()
    {
        // $this->getServiceLocator()->get('Zend\Log')->info('ajaxloginAction -
        // AUTHENT : ');
        if ($this->zfcUserAuthentication()
            ->getAuthService()
            ->hasIdentity()) {
            return true;
        }
        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()
            ->fromQuery('redirect', false));

        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        /*
         * if ($result instanceof Response) { return $result; }
         */

        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        if (! $auth->isValid()) {
            $adapter->resetAdapters();

            return false;
        }

        $user = $this->zfcUserAuthentication()->getIdentity();

        if ($user->getState() && $user->getState() === 2) {
            $this->getUserService()->getUserMapper()->activate($user);
        }
        $this->getEventManager()->trigger('login.post', $this, array('user' => $user));
        return true;
    }

    public function providerLoginAction()
    {
        $provider = $this->getEvent()->getRouteMatch()->getParam('provider');
        if (!in_array($provider, $this->getUserService()->getOptions()->getEnabledProviders())) {
            return $this->notFoundAction();
        }

        $hybridAuth = $this->getHybridAuth();

        $query = 'provider=' . $provider;
        if ($this->getServiceLocator()->get('zfcuser_module_options')->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect')) {
            $query .= '&redirect=' . $this->getRequest()->getQuery()->get('redirect');
        }

        $redirectUrl = $this->url()->fromRoute('frontend/zfcuser/authenticate') . '?' . $query;

        $adapter = $hybridAuth->authenticate(
            $provider,
            array('hauth_return_to' => $redirectUrl)
        );

        $go = $this->frontendUrl()->fromRoute('');
        return $this->redirect()->toUrl($go);
    }

    public function logoutAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        $hybridAuth = $this->getHybridAuth();

        Hybrid_Auth::logoutAllProviders();

        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        // before logout, a user was connected
        if ($user) {
            $this->getEventManager()->trigger('logout.post', $this, array('user' => $user));
        }

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->redirect()->toRoute($this->getOptions()->getLogoutRedirectRoute());
    }

    /**
     * General-purpose authentication action
     */
    public function authenticateAction()
    {
        if ($this->zfcUserAuthentication()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));
        $routeLoginAdmin = $this->params()->fromPost('routeLoginAdmin', $this->params()->fromQuery('routeLoginAdmin', false));

        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }

        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        if (!$auth->isValid()) {
            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);
            $adapter->resetAdapters();

            if (!empty($routeLoginAdmin)) {
                return $this->redirect()->toUrl($routeLoginAdmin);
            }

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/login')
                . ($redirect ? '?redirect='.$redirect : ''));
        }

        $user = $this->zfcUserAuthentication()->getIdentity();
        $this->getEventManager()->trigger('login.post', $this, array('user' => $user));

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->redirect()->toUrl(
            $this->url()->fromRoute(
                $this->getOptions()->getLoginRedirectRoute()
            )
        );
    }

    /**
     * user profile
     * Management of 4 differents forms...
     * TODO : Refactor it ! this is uuuuuugly !
     */
    public function profileAction()
    {
        $translator = $this->getServiceLocator()->get('translator');
        if (! $this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toUrl(
                $this->url()->fromRoute(
                    $this->getOptions()->getLoginRedirectRoute(),
                    array(),
                    array('force_canonical' => true)
                )
            );
        }
        $formEmail     = $this->getChangeEmailForm();
        $formEmail->get('credential')
                  ->setLabel($translator->translate('Your password', 'playgrounduser'))
                  ->setAttributes(array(
                      'type'            => 'password',
                      'class'        => 'large-input',
                      'placeholder'    => $translator->translate('Your password', 'playgrounduser')
                  ));
        $formEmail->get('newIdentity')
                  ->setLabel($translator->translate('Your new email', 'playgrounduser'))
                  ->setAttributes(array(
                      'type'            => 'email',
                      'class'        => 'large-input',
                      'placeholder'    => $translator->translate('Your new email', 'playgrounduser')
                  ));
        $formEmail->get('newIdentityVerify')
                  ->setLabel($translator->translate('Confirm the new email', 'playgrounduser'))
                  ->setAttributes(array(
                      'type'            => 'email',
                      'class'        => 'large-input',
                      'placeholder'    => $translator->translate('Confirm the new email', 'playgrounduser')
                  ));
        $formPassword  = $this->getChangePasswordForm();
        $formPassword->get('credential')
                     ->setLabel($translator->translate('Your current password', 'playgrounduser'))
                     ->setAttributes(array(
                         'class'    => 'large-input',
                         'type'        => 'password',
                         'placeholder' => $translator->translate('Your current password', 'playgrounduser')
                     ));
        $formPassword->get('newCredential')
                     ->setLabel($translator->translate('New Password', 'playgrounduser'))
                     ->setAttributes(array(
                         'class'    => 'large-input',
                         'type'        => 'password',
                         'placeholder' => $translator->translate('New Password', 'playgrounduser')
                     ));
        $formPassword->get('newCredentialVerify')
                     ->setLabel($translator->translate('Verify New Password', 'playgrounduser'))
                     ->setAttributes(array(
                         'class'    => 'large-input',
                         'type'        => 'password',
                         'placeholder' => $translator->translate('Verify New Password', 'playgrounduser')
                     ));
        ;
        $formInfo      = $this->getChangeInfoForm();
        $formPrize     = $this->getPrizeCategoryForm();
        $formBlock     = $this->getBlockAccountForm();
        if ($this->zfcUserAuthentication()->getIdentity()->getState() == 2) {
            $formBlock->get('activate')->setAttribute('value', 1);
            $formBlock->get('submit')->setAttribute('value', $translator->translate('Reactivate my account', 'playgrounduser'));
            $formBlock->get('confirm_submit')->setAttribute('value', $translator->translate('Confirm account reactivation', 'playgrounduser'));
        }

        $categoryService = $this->getServiceLocator()->get('playgroundgame_prizecategoryuser_service');
        $categoriesUser = $categoryService->getPrizeCategoryUserMapper()->findBy(array('user' => $this->zfcUserAuthentication()->getIdentity()));
        $existingCategories = array();

        foreach ($categoriesUser as $categoryUser) {
            $existingCategories[] = $categoryUser->getPrizeCategory()->getId();
        }

        $formPrize->get('prizeCategory')->setAttribute('value', $existingCategories);

        $request = $this->getRequest();
        // I don't want to rely on the browser's info for these key datas
        $request->getPost()->set('identity', $this->getUserService()
            ->getAuthService()
            ->getIdentity()
            ->getEmail());
        $request->getPost()->set('email', $this->getUserService()
            ->getAuthService()
            ->getIdentity()
            ->getEmail());
        $userId = $this->getUserService()
            ->getAuthService()
            ->getIdentity()
            ->getId();

        $user = $this->getUserService()
            ->getUserMapper()
            ->findById($userId);
        $formInfo->bind($user);

        $username = $formInfo->get('username')->getValue();
        $userFirstLastName = $user->getFirstName().' '.substr($user->getLastName(), 0, 1);
        if (empty($username) || $username == $userFirstLastName) {
            $usernamePoint = '+ 150 pts';
        } else {
            $usernamePoint = '';
        }

        $fmPassword = $this->flashMessenger()
            ->setNamespace('change-password')
            ->getMessages();

        if (isset($fmPassword[0])) {
            $statusPassword = $fmPassword[0];
        } else {
            $statusPassword = null;
        }

        $fmEmail = $this->flashMessenger()
            ->setNamespace('change-email')
            ->getMessages();
        if (isset($fmEmail[0])) {
            $statusEmail = $fmEmail[0];
        } else {
            $statusEmail = null;
        }

        $fmInfo = $this->flashMessenger()
            ->setNamespace('change-info')
            ->getMessages();
        if (isset($fmInfo[0])) {
            $statusInfo = $fmInfo[0];
        } else {
            $statusInfo = null;
        }

        if ($request->isPost() && array_key_exists('firstname', $this->params()->fromPost())) {
            $result = false;
            $data = $request->getPost()->toArray();
            $file = $this->params()->fromFiles('avatar');
            if ($file['name']) {
                $data = array_merge($data, array(
                    'avatar' => $file['name']
                ));
            }

            $result = $this->getUserService()->updateInfo($data, $user);

            if (! $result) {
                return array(
                    'statusPassword' => null,
                    'changePasswordForm' => $formPassword,
                    'statusEmail' => null,
                    'changeEmailForm' => $formEmail,
                    'statusInfo' => false,
                    'changeInfoForm' => $formInfo,
                    'prizeCategoryForm' => $formPrize,
                    'blockAccountForm' => $formBlock,
                    'usernamePoint' => $usernamePoint,
                );
            }

            $this->flashMessenger()
                ->setNamespace('change-info')
                ->addMessage(true);

            return $this->redirect()->toUrl(
                $this->url()->fromRoute(
                    'frontend/zfcuser/profile',
                    array(),
                    array('force_canonical' => true)
                )
            );
        }

        $redirectUrl = $this->url()->fromRoute(
            'frontend/zfcuser/profile',
            array(
                'statusPassword' => $statusPassword,
                'statusEmail' => $statusEmail,
                'statusInfo' => $statusInfo,
            )
        );
        $prg = $this->prg($redirectUrl, true);
//         $prg = $this->prg('frontend/zfcuser/profile');

        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return array(
            'statusPassword' => $statusPassword,
            'changePasswordForm' => $formPassword,
            'statusEmail' => $statusEmail,
            'changeEmailForm' => $formEmail,
            'statusInfo' => $statusInfo,
            'changeInfoForm' => $formInfo,
            'prizeCategoryForm' => $formPrize,
            'blockAccountForm' => $formBlock,
            'usernamePoint' => $usernamePoint,
            );
        }

        if (isset($prg['newCredential'])) {
            $formPassword->setData($prg);
            if (! $formPassword->isValid()) {
                $messages = $formPassword->getMessages();
                if (isset($messages['credential']) && isset($messages['credential']['isEmpty'])) {
                    $messages['credential']['isEmpty'] = 'Saisissez votre mot de passe actuel';
                }
                if (isset($messages['newCredential']) && isset($messages['newCredential']['isEmpty'])) {
                    $messages['newCredential']['isEmpty'] = 'Saisissez votre nouveau mot de passe';
                }
                if (isset($messages['newCredentialVerify']) && isset($messages['newCredentialVerify']['isEmpty'])) {
                    $messages['newCredentialVerify']['isEmpty'] = 'Confirmation du mot de passe ';
                }
                $formPassword->setMessages($messages);

                return array(
                    'statusPassword' => false,
                    'changePasswordForm' => $formPassword,
                    'statusEmail' => null,
                    'changeEmailForm' => $formEmail,
                    'statusInfo' => null,
                    'changeInfoForm' => $formInfo,
                    'prizeCategoryForm' => $formPrize,
                    'blockAccountForm' => $formBlock,
                    'usernamePoint' => $usernamePoint,
                    );
            }

            if (! $this->getUserService()->changePassword($formPassword->getData())) {
                return array(
                'statusPassword' => false,
                'changePasswordForm' => $formPassword,
                'statusEmail' => null,
                'changeEmailForm' => $formEmail,
                'statusInfo' => null,
                'changeInfoForm' => $formInfo,
                'prizeCategoryForm' => $formPrize,
                'blockAccountForm' => $formBlock,
                'usernamePoint' => $usernamePoint,
                );
            }

            $this->flashMessenger()
                ->setNamespace('change-password')
                ->addMessage(true);

            return $this->redirect()->toUrl(
                $this->url()->fromRoute(
                    'frontend/zfcuser/profile',
                    array(),
                    array('force_canonical' => true)
                )
            );
        } elseif (isset($prg['newIdentity'])) {
            $formEmail->setData($prg);

            if (! $formEmail->isValid()) {
                $messages = $formEmail->getMessages();
                if (isset($messages['newIdentity']) && isset($messages['newIdentity']['isEmpty'])) {
                    $messages['newIdentity']['isEmpty'] = 'Saisissez votre nouvel email';
                }
                if (isset($messages['newIdentity']) && isset($messages['newIdentity']['recordFound'])) {
                    $messages['newIdentity']['recordFound'] = 'Cet email existe déjà';
                }
                if (isset($messages['newIdentityVerify']) && isset($messages['newIdentityVerify']['isEmpty'])) {
                    $messages['newIdentityVerify']['isEmpty'] = 'Confirmer votre nouvel email';
                }
                if (isset($messages['newIdentityVerify']) && isset($messages['newIdentityVerify']['notSame'])) {
                    $messages['newIdentityVerify']['notSame'] = 'Les deux emails ne correspondent pas';
                }
                $formEmail->setMessages($messages);

                return array(
                'statusPassword' => null,
                'changePasswordForm' => $formPassword,
                'statusEmail' => false,
                'changeEmailForm' => $formEmail,
                'statusInfo' => null,
                'changeInfoForm' => $formInfo,
                'prizeCategoryForm' => $formPrize,
                'blockAccountForm' => $formBlock,
                'usernamePoint' => $usernamePoint,
                );
            }

            $change = $this->getUserService()->changeEmail($prg);

            if (! $change) {
                $this->flashMessenger()
                ->setNamespace('change-email')
                ->addMessage(false);

                return array(
                'statusPassword' => null,
                'changePasswordForm' => $formPassword,
                'statusEmail' => false,
                'changeEmailForm' => $formEmail,
                'statusInfo' => null,
                'changeInfoForm' => $formInfo,
                'prizeCategoryForm' => $formPrize,
                'blockAccountForm' => $formBlock,
                'usernamePoint' => $usernamePoint,
                );
            }

            $this->flashMessenger()
            ->setNamespace('change-email')
            ->addMessage(true);

            return $this->redirect()->toUrl(
                $this->url()->fromRoute(
                    'frontend/zfcuser/profile',
                    array(),
                    array('force_canonical' => true)
                )
            );
        }

        return array(
            'statusPassword' => null,
            'changePasswordForm' => $formPassword,
            'statusEmail' => null,
            'changeEmailForm' => $formEmail,
            'statusInfo' => null,
            'changeInfoForm' => $formInfo,
            'prizeCategoryForm' => $formPrize,
            'blockAccountForm' => $formBlock,
            'usernamePoint' => $usernamePoint,
            );
    }

    /**
     * address
     */
    public function addressAction()
    {
        if (! $this->zfcUserAuthentication()->hasIdentity()) {
            return null;
        }
        $form = $this->getAddressForm();
        //$form->setAttribute('action', '');

        $request = $this->getRequest();
        // I don't want to rely on the browser's info for these key datas
        $request->getPost()->set('identity', $this->getUserService()
                ->getAuthService()
                ->getIdentity()
                ->getEmail());

        $email = $request->getPost()->get('email');
        if (empty($email)) {
            $request->getPost()->set('email', $this->getUserService()
                    ->getAuthService()
                    ->getIdentity()
                    ->getEmail());
        }

        $userId = $this->getUserService()
            ->getAuthService()
            ->getIdentity()
            ->getId();

        $user = $this->getUserService()->getUserMapper()->findById($userId);
        $form->bind($user);

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            $result = $this->getUserService()->updateAddress($data, $user);
            if ($result) {
                return true;
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('form' => $form));

        return $viewModel;
    }

    /**
     * Register a user from a social channel (only Facebook has been tested)
     */
    public function registerFacebookUserAction()
    {
        // The provider has to be set in the querystring of the request for hybridauth to work properly.
        $provider = $this->params()->fromRoute('provider');
        $this->getRequest()->getQuery()->provider = $provider;

        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();
        $adapter->prepareForAuthentication($this->getRequest());

        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        $user = $this->zfcUserAuthentication()->getIdentity();

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('user' => $user));

        return $viewModel;
    }

    public function blockAccountAction()
    {
        // if the user isn't logged in, we can't change password
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toUrl(
                $this->url()->fromRoute('frontend/zfcuser/profile')
            );
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            if ($this->getUserService()->blockAccount($data)) {
                $this->flashMessenger()->setNamespace('block-account')->addMessage(true);
            }
        }

        return $this->redirect()->toUrl(
            $this->url()->fromRoute('frontend/zfcuser/profile')
        );
    }

    public function prizeCategoryUserAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $service = $this->getServiceLocator()->get('playgroundgame_prizecategoryuser_service');
            $result = $service->edit($data, $this->zfcUserAuthentication()->getIdentity(), 'playgroundgame_prizecategoryuser_form');
            if ($result) {
                $this->flashMessenger()
                    ->setNamespace('playgroundgame')
                    ->addMessage('La catégorie a été mise à jour');
            }
        }

        return $this->redirect()->toUrl(
            $this->url()->fromRoute('frontend/zfcuser/profile')
        );
    }

    /**
     * Newsletter
     */
    public function newsletterAction()
    {
        // if the user isn't logged in, we can't change password
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toUrl(
                $this->url()->fromRoute('frontend/zfcuser/profile')
            );
        }
        $userId = $this->getUserService()
        ->getAuthService()
        ->getIdentity()
        ->getId();

        $user = $this->getUserService()
            ->getUserMapper()
            ->findById($userId);

        $viewModel = new ViewModel();

        $request = $this->getRequest();
        $service = $this->getUserService();
        $form = $this->getNewsletterForm();
        $form->bind($user);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            if ($this->getUserService()->updateNewsletter($data)) {
                $this->flashMessenger()->setNamespace('newsletter')->addMessage(true);
            }
        }
        $viewModel->setVariables(array('form' => $form));

        return $viewModel;
    }

    public function ajaxNewsletterAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));
        } else {
            if ($request->isPost()) {
                $data = $this->getRequest()->getPost()->toArray();
                $data['optinPartner'] = $this->zfcUserAuthentication()->getIdentity()->getOptinPartner();

                if ($this->getUserService()->updateNewsletter($data)) {
                    $response->setContent(\Zend\Json\Json::encode(array(
                        'success' => 1
                    )));
                } else {
                    $response->setContent(\Zend\Json\Json::encode(array(
                        'success' => 0
                    )));
                }
            }
        }

        return $response;
    }

    public function checkTokenAction()
    {
        $service = $this->getUserService();
        $service->cleanExpiredVerificationRequests();

        // Pull and validate the Request Key
        $token = $this->getRequest()->getQuery()->get('token');
        //$token = $this->plugin('params')->fromRoute('token');
        $validator = new \Zend\Validator\Hex();
        if (!$validator->isValid($token)) {
            throw new \InvalidArgumentException('Invalid Token!');
        }

        // Find the request key in the database
        $validation = $service->findByRequestKey($token);
        if (! $validation) {
            //throw new \InvalidArgumentException('Invalid Token r!');
            return $this->redirect()->toUrl(
                $this->url()->fromRoute('frontend')
            );
        }

        return $this->forward()->dispatch('playgrounduser_user', array(
            'action' => 'authenticate'
        ));
    }

    /**
     * user registermail
     */
    public function registermailAction()
    {
        $viewModel = new ViewModel();

        return $viewModel;
    }

    /**
     * Get changeEmailForm.
     *
     * @return changeEmailForm.
     */
    public function getChangeInfoForm()
    {
        if (! $this->changeInfoForm) {
            $this->setChangeInfoForm($this->getServiceLocator()
                ->get('playgrounduser_change_info_form'));
        }

        return $this->changeInfoForm;
    }

    /**
     * Set changeEmailForm.
     *
     * @param
     *            changeEmailForm the value to set.
     */
    public function setChangeInfoForm($changeInfoForm)
    {
        $this->changeInfoForm = $changeInfoForm;

        return $this;
    }

    /**
     * Get prizeCategoryForm.
     *
     * @return prizeCategoryForm.
     */
    public function getPrizeCategoryForm()
    {
        if (! $this->prizeCategoryForm) {
            $this->setPrizeCategoryForm($this->getServiceLocator()
                ->get('playgroundgame_prizecategoryuser_form'));
        }

        return $this->prizeCategoryForm;
    }

    /**
     * Set prizeCategoryForm.
     *
     * @param
     *            prizeCategoryForm the value to set.
     */
    public function setPrizeCategoryForm($prizeCategoryForm)
    {
        $this->prizeCategoryForm = $prizeCategoryForm;

        return $this;
    }

    /**
     * Get blockAccountForm.
     *
     * @return blockAccountForm.
     */
    public function getBlockAccountForm()
    {
        if (! $this->blockAccountForm) {
            $this->setBlockAccountForm($this->getServiceLocator()
                    ->get('playgrounduser_blockaccount_form'));
        }

        return $this->blockAccountForm;
    }

    /**
     * Set blockAccountForm.
     *
     * @param  blockAccountForm the value to set.
     */
    public function setBlockAccountForm($blockAccountForm)
    {
        $this->blockAccountForm = $blockAccountForm;

        return $this;
    }

    /**
     * Get newsletterForm.
     *
     * @return newsletterForm.
     */
    public function getNewsletterForm()
    {
        if (! $this->newsletterForm) {
            $this->setNewsletterForm($this->getServiceLocator()
                    ->get('playgrounduser_newsletter_form'));
        }

        return $this->newsletterForm;
    }

    /**
     * Set newsletterForm.
     *
     * @param  newsletterForm the value to set.
     */
    public function setNewsletterForm($newsletterForm)
    {
        $this->newsletterForm = $newsletterForm;

        return $this;
    }

    /**
     * Get addressForm.
     *
     * @return addressForm.
     */
    public function getAddressForm()
    {
        if (! $this->addressForm) {
            $this->setAddressForm($this->getServiceLocator()
                    ->get('playgrounduser_address_form'));
        }

        return $this->addressForm;
    }

    /**
     * Set addressForm.
     *
     * @param  addressForm the value to set.
     */
    public function setAddressForm($addressForm)
    {
        $this->addressForm = $addressForm;

        return $this;
    }

    /**
     * Service Provider
     * @var
     */
    protected $providerService;

    /**
     * initialisation du service
     * @param  $service
     */
    public function setProviderService($service)
    {
        $this->providerService = $service;
    }

    /**
     * retourne le service social
     * @return
     */
    public function getProviderService()
    {
        if ($this->providerService == null) {
            $this->setProviderService($this->getServiceLocator()->get('playgrounduser_provider_service'));
        }

        return $this->providerService;
    }

    /**
     * Get the Hybrid_Auth object
     *
     * @return Hybrid_Auth
     */
    public function getHybridAuth()
    {
        if (!$this->hybridAuth) {
            $this->hybridAuth = $this->getServiceLocator()->get('HybridAuth');
        }

        return $this->hybridAuth;
    }

    /**
     * Set the Hybrid_Auth object
     *
     * @param  Hybrid_Auth    $hybridAuth
     * @return UserController
     */
    public function setHybridAuth(Hybrid_Auth $hybridAuth)
    {
        $this->hybridAuth = $hybridAuth;

        return $this;
    }

    protected function getViewHelper($helperName)
    {
        return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);
    }

    // TODO : remove asap this adherence
    public function getCoreOptions()
    {
        if (!$this->coreOptions) {
            $this->setCoreOptions($this->getServiceLocator()->get('playgroundcore_module_options'));
        }

        return $this->coreOptions;
    }

    public function setCoreOptions($options)
    {
        $this->coreOptions = $options;

        return $this;
    }

    /**
     * TODO remove this F&@king adherence with PlaygroundReward...
     */
    public function getRewardService()
    {
        if (!$this->rewardService) {
            $this->rewardService = $this->getServiceLocator()->get('playgroundreward_event_service');
        }

        return $this->rewardService;
    }

    public function setRewardService($rewardService)
    {
        $this->rewardService = $rewardService;

        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->getServiceLocator();
    }
}
