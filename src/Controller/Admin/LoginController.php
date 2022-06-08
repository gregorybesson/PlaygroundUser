<?php
namespace PlaygroundUser\Controller\Admin;

use Hybrid_Auth;
use Laminas\Form\Form;
use Laminas\Stdlib\ResponseInterface as Response;
use Laminas\Stdlib\Parameters;
use LmcUser\Controller\UserController as LmcUserController;
use Laminas\View\Model\ViewModel;
use Laminas\ServiceManager\ServiceLocatorInterface;

class LoginController extends LmcUserController
{
    protected $options = null;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
        $redirectCallback = $locator->get('lmcuser_redirect_callback');
        parent::__construct($redirectCallback);
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        $user = $this->lmcuserAuthentication()->getIdentity();
        $authAdminConfig = $this->getOptions()->getAdmin();

        if ($user && $this->isAllowed($authAdminConfig['resource'], $authAdminConfig['privilege'])) {
            return $this->forward()->dispatch($authAdminConfig['controller'], array('action' => $authAdminConfig['action']));
        }

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if (!$form->isValid()) {
                $this->flashMessenger()->setNamespace('lmcuser-login-form')->addMessage($this->failedLoginMessage);

                return $this->redirect()->toUrl($this->url()->fromRoute($authAdminConfig['route_login']));
            }

            // clear adapters
            $this->lmcuserAuthentication()->getAuthAdapter()->resetAdapters();
            $this->lmcuserAuthentication()->getAuthAdapter()->logoutAdapters();
            $this->lmcuserAuthentication()->getAuthService()->clearIdentity();

            $request->getQuery()->redirect = $this->url()->fromRoute($authAdminConfig['route_login']);
            $request->getQuery()->routeLoginAdmin = $authAdminConfig['route_login'];

            return $this->forward()->dispatch("playgrounduser_user", array('action' => 'authenticate'));
        }

        return array(
            'loginForm' => $form,
        );
    }

    public function logoutAction()
    {
        $user = $this->lmcuserAuthentication()->getIdentity();

        $this->lmcuserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->lmcuserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->lmcuserAuthentication()->getAuthService()->clearIdentity();

        if ($user) {
            $this->getEventManager()->trigger('logout.post', $this, array('user' => $user));
        }

        return $this->redirect()->toUrl($this->url()->fromRoute('admin'));
    }

    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = $this->getServiceLocator()->get('playgrounduser_module_options');
        }

        return  $this->options;
    }
}
