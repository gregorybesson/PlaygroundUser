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

    /**
     * Login form
     */
    public function loginAction()
    {

        $config = $this->getServiceLocator()->get('Config');
        $playgroundAuth = $config['playgroundAuth'];
        
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        $user = $this->zfcUserAuthentication()->getIdentity();
        if($user && $this->isAllowed($playgroundAuth['loginSuccess']['resource'], $playgroundAuth['loginSuccess']['privilege'])){
        	// TODO : Make this road configurable and remove the adherence with adminstats.
        	return $this->forward()->dispatch($playgroundAuth['loginSuccess']['controller'], array('action' => $playgroundAuth['loginSuccess']['action']));
        }

        if ($request->isPost()) {
	        $form->setData($request->getPost());

	        if (!$form->isValid()) {
	            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

	            return $this->redirect()->toUrl($this->url()->fromRoute($playgroundAuth['routeLogin']));
	        }

	        // clear adapters
	        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
	        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();


	        $request->getQuery()->redirect = $this->url()->fromRoute($playgroundAuth['routeLogin']);

	        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
        }

        return array(
            'loginForm' => $form,
        );
    }
}
