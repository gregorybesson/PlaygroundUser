<?php

namespace PlaygroundUser\Controller\Frontend;

use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use PlaygroundUser\Service\Password as PasswordService;
use PlaygroundUser\Options\ForgotControllerOptionsInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ForgotController extends AbstractActionController
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var PasswordService
     */
    protected $passwordService;

    /**
     * @var Form
     */
    protected $forgotForm;

    /**
     * @var Form
     */
    protected $resetForm;

    /**
     *
     * @var string
     */
    protected $message = 'An e-mail with further instructions has been sent to you.';

    /**
     *
     * @var string
     */
    protected $failedMessage = 'The e-mail address is not valid.';

    /**
     * @var ForgotControllerOptionsInterface
     */
    protected $options;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * User page
     */
    public function indexAction()
    {
        //$this->getServiceLocator()->get('Laminas\Log')->info('ForgotAction...');
        if ($this->lmcuserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute('frontend/lmcuser');
        } else {
            return $this->redirect()->toRoute('frontend/lmcuser/forgotpassword');
        }
    }

    public function forgotAction()
    {
        $service = $this->getPasswordService();
        $service->cleanExpiredForgotRequests();
        $form = $this->getForgotForm();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $email = $form->getData();
                $email = $email['email'];

                return $this->redirect()->toRoute('frontend/lmcuser/sentpassword', array("email"=> $email));
            } else {
                $this->flashMessenger()->setNamespace('playgrounduser-forgot-form')->addMessage($this->failedMessage);

                return array(
                    'forgotForm' => $form,
                );
            }
        }

        // Render the form
        return array(
            'forgotForm' => $form,
        );
    }

    public function ajaxforgotAction()
    {
        $response = $this->getResponse();
        $service = $this->getPasswordService();
        $service->cleanExpiredForgotRequests();
        $form = $this->getForgotForm();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $email = $form->getData();
                $email = $email['email'];

                $user = $this->getUserService()->getUserMapper()->findByEmail($email);

                //only send request when email is found
                if ($user != null) {
                    $this->getPasswordService()->sendProcessForgotRequest($user, $email);
                    $response->setContent(\Laminas\Json\Json::encode(array(
                        'statusMail' => true,
                        'email' => $email
                    )));
                } else {
                    $response->setContent(\Laminas\Json\Json::encode(array(
                        'statusMail' => false,
                        'email' => $email
                    )));
                }
            } else {
                $response->setContent(\Laminas\Json\Json::encode(array(
                    'statusMail' => false,
                    'email' => $email
                )));
            }
        } else {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'statusMail' => false,
                'email' => ''
            )));
        }

        return $response;
    }

    public function ajaxrenewPasswordAction()
    {
        $response = $this->getResponse();

        $email = ($this->params()->fromPost('email'))?$this->params()->fromPost('email'):null;
        $user = $this->getUserService()->getUserMapper()->findByEmail($email);

        if ($this->getRequest()->isPost() && $user != null) {
            $password = strtolower(substr(sha1(uniqid('gb', true).'####'.time()), 0, 7));
            $this->getPasswordService()->resetPassword(new \PlaygroundUser\Entity\Password, $user, array('newCredential' => $password));

            $this->getPasswordService()->sendForgotEmailMessage($email, $password);

            $response->setContent(\Laminas\Json\Json::encode(array(
                'statusMail' => true,
                'email' => $password
            )));
        } else {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'statusMail' => false,
                'email' => $email
            )));
        }

        return $response;
    }

    public function sentAction()
    {
        $email = $this->getEvent()->getRouteMatch()->getParam('email');
        $user = $this->getUserService()->getUserMapper()->findByEmail($email);

        $vm = new ViewModel();
        //only send request when email is found
        if ($user != null) {
            $this->getPasswordService()->sendProcessForgotRequest($user, $email);
            $vm->setVariables(array(
                'statusMail' => true,
                'email' => $email
            ));
        } else {
            $vm->setVariables(array(
                'statusMail' => false,
                'email' => $email
            ));
        }

        return $vm;
    }

    public function resetAction()
    {
        $service = $this->getPasswordService();
        $service->cleanExpiredForgotRequests();
        $form    = $this->getResetForm();

        $userId    = $this->params()->fromRoute('userId', null);
        $token     = $this->params()->fromRoute('token', null);

        $password = $service->getPasswordMapper()->findByUserIdRequestKey($userId, $token);

        //no request for a new password found
        if ($password === null) {
            return $this->redirect()->toRoute('frontend/lmcuser/forgotpassword', array("userId"=> $userId));
        }

        $userService = $this->getUserService();
        $user = $userService->getUserMapper()->findById($userId);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid() && $user !== null) {
                $service->resetPassword($password, $user, $form->getData());
                return $this->redirect()->toRoute(
                    'frontend/lmcuser/passwordchanged',
                    array('userId' => $userId),
                    array('force_canonical'=>true)
                );
            }
        }

        // Render the form
        return array(
            'resetForm' => $form,
            'userId'    => $userId,
            'token'     => $token,
            'email'     => $user->getEmail(),
        );
    }

    public function passwordChangedAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserService()->getUserMapper()->findById($userId);

        return new ViewModel(array('email' => $user->getEmail()));
    }

    /**
     * Getters/setters for DI stuff
     */

    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceLocator()->get('lmcuser_user_service');
        }

        return $this->userService;
    }

    public function setUserService(\PlaygroundUser\Service\User $userService)
    {
        $this->userService = $userService;

        return $this;
    }

    public function getPasswordService()
    {
        if (!$this->passwordService) {
            $this->passwordService = $this->getServiceLocator()->get('playgrounduser_password_service');
        }

        return $this->passwordService;
    }

    public function setPasswordService(\PlaygroundUser\Service\Password $passwordService)
    {
        $this->passwordService = $passwordService;

        return $this;
    }

    public function getForgotForm()
    {
        if (!$this->forgotForm) {
            $this->setForgotForm($this->getServiceLocator()->get('playgrounduser_forgot_form'));
        }

        return $this->forgotForm;
    }

    public function setForgotForm(Form $forgotForm)
    {
        $this->forgotForm = $forgotForm;
    }

    public function getResetForm()
    {
        if (!$this->resetForm) {
            $this->setResetForm($this->getServiceLocator()->get('playgrounduser_reset_form'));
        }

        return $this->resetForm;
    }

    public function setResetForm(Form $resetForm)
    {
        $this->resetForm = $resetForm;
    }

    /**
     * set options
     *
     * @param  ForgotControllerOptionsInterface $options
     * @return ForgotController
     */
    public function setOptions(ForgotControllerOptionsInterface $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * get options
     *
     * @return ForgotControllerOptionsInterface
     */
    public function getOptions()
    {
        if (!$this->options instanceof ForgotControllerOptionsInterface) {
            $this->setOptions($this->getServiceLocator()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }
}
