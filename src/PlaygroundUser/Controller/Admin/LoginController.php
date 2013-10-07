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
        if($user && $this->isAllowed($this->getOptions()->getResource(), $this->getOptions()->getPrivilege())){
        	return $this->forward()->dispatch($this->getOptions()->getController(), array('action' => $this->getOptions()->getAction()));
        }

        if ($request->isPost()) {
	        $form->setData($request->getPost());

	        if (!$form->isValid()) {
	            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

	            return $this->redirect()->toUrl($this->url()->fromRoute($this->getOptions()->getRouteLogin()));
	        }

	        // clear adapters
	        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
	        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();


	        $request->getQuery()->redirect = $this->url()->fromRoute($this->getOptions()->getRouteLogin());

	        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
        }

        return array(
            'loginForm' => $form,
        );
    }

    public function getOptions()
    {
        if($this->options === null){
            $this->options = $this->getServiceLocator()->get('playgrounduser_module_options');
        }

        return  $this->options;
    }
}
