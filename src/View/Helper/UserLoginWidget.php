<?php

namespace PlaygroundUser\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use PlaygroundUser\Form\Login as LoginForm;
use Laminas\View\Model\ViewModel;

class UserLoginWidget extends AbstractHelper
{
    /**
     * Login Form
     * @var LoginForm
     */
    protected $loginForm;

    /**
     * $var string template used for view
     */
    protected $viewTemplate;

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {
        if (array_key_exists('render', $options)) {
            $render = $options['render'];
        } else {
            $render = true;
        }
        if (array_key_exists('redirect', $options)) {
            $redirect = $options['redirect'];
        } else {
            $redirect = false;
        }
        if (array_key_exists('enableRegistration', $options)) {
            $enableRegistration = $options['enableRegistration'];
        } else {
            $enableRegistration = false;
        }
        if (array_key_exists('template', $options)) {
            $template = $options['template'];
        } else {
            $template = $this->viewTemplate;
        }

        $vm = new ViewModel(array(
            'loginForm' => $this->getLoginForm(),
            'redirect'  => $redirect,
            'enableRegistration' => $enableRegistration
        ));
        $vm->setTemplate($template);
        if ($render) {
            return $this->getView()->render($vm);
        } else {
            return $vm;
        }
    }

    /**
     * Retrieve Login Form Object
     * @return LoginForm
     */
    public function getLoginForm()
    {
        return $this->loginForm;
    }

    /**
     * Inject Login Form Object
     * @param  LoginForm          $loginForm
     * @return ZfcUserLoginWidget
     */
    public function setLoginForm(LoginForm $loginForm)
    {
        $this->loginForm = $loginForm;

        return $this;
    }

    /**
     * @param  string             $viewTemplate
     * @return ZfcUserLoginWidget
     */
    public function setViewTemplate($viewTemplate)
    {
        $this->viewTemplate = $viewTemplate;

        return $this;
    }
}
