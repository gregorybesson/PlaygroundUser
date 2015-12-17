<?php
namespace PlaygroundUser\Controller\Admin;

use Hybrid_Auth;
use Zend\Form\Form;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use ZfcUser\Controller\UserController as ZfcUserController;
use Zend\View\Model\ViewModel;

class LoginController extends ZfcUserController
{
    protected $options = null;
    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        $user = $this->zfcUserAuthentication()->getIdentity();
        $authAdminConfig = $this->getOptions()->getAdmin();

        if ($user && $this->isAllowed($authAdminConfig['resource'], $authAdminConfig['privilege'])) {
            return $this->forward()->dispatch($authAdminConfig['controller'], array('action' => $authAdminConfig['action']));
        }

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if (!$form->isValid()) {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

                return $this->redirect()->toUrl($this->url()->fromRoute($authAdminConfig['route_login']));
            }

            // clear adapters
            $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
            $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

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
        $user = $this->zfcUserAuthentication()->getIdentity();

        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

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
