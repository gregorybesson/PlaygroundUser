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
    const ROUTE_DASHBOARD    = 'admin/dashboard';
    const ROUTE_LOGIN        = 'admin';

    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();
        $redirect = $this->url()->fromRoute(static::ROUTE_DASHBOARD);

        $user = $this->zfcUserAuthentication()->getIdentity();
        if($user && $this->isAllowed('core', 'dashboard')){
        	// TODO : Make this road configurable and remove the adherence with adminstats.
        	return $this->forward()->dispatch('adminstats', array('action' => 'index'));
        }

        if ($request->isPost()) {
	        $form->setData($request->getPost());

	        if (!$form->isValid()) {
	            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

	            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN));
	        }

	        // clear adapters
	        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
	        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();


	        $request->getQuery()->redirect = $this->url()->fromRoute(static::ROUTE_LOGIN);;

	        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
        }

        return array(
            'loginForm' => $form,
        );
    }
}
