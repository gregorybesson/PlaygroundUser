<?php

namespace PlaygroundUser\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use ZfcUser\Options\AuthenticationOptionsInterface;
use ZfcUser\Module as ZfcUser;
use Zend\Mvc\I18n\Translator;

class Login extends ProvidesEventsForm
{
    /**
     * @var AuthenticationOptionsInterface
     */
    protected $authOptions;

    public function __construct($name, AuthenticationOptionsInterface $options, Translator $translator)
    {
        $this->setAuthenticationOptions($options);
        parent::__construct($name);

        $this->add(array(
            'name' => 'identity',
            'options' => array(
                'label' => '',
            ),
            'attributes' => array(
                'type' => 'email',
                'class' => 'large-input',
                'placeholder' => ($translator->translate('Email address', 'playgrounduser')),
            ),
        ));

        $emailElement = $this->get('identity');
        $label = $emailElement->getLabel('label');
        // @TODO: make translation-friendly
        foreach ($this->getAuthenticationOptions()->getAuthIdentityFields() as $mode) {
            $label = (!empty($label) ? $label . ' or ' : '') . ucfirst($mode);
        }
        $emailElement->setLabel($label);
        //
        $this->add(array(
            'name' => 'credential',
            'options' => array(
                'label' => ($translator->translate('Password', 'playgrounduser')),
            ),
            'attributes' => array(
                'type' => 'password',
                'class' => 'large-input',
                'placeholder' => ($translator->translate('Password', 'playgrounduser')),
            ),
        ));

        //remember_me
        // TODO : le rendre optionnel
        $this->add(array(
             'type' => 'Zend\Form\Element\Checkbox',
             'name' => 'remember_me',
             'options' => array(
                     'label' => 'Stay logged in',
                     'use_hidden_element' => true,
                     'checked_value' => '1',
                     'unchecked_value' => '0'
             )
        ));

        // @todo: Fix this
        // 1) getValidator() is a protected method
        // 2) i don't believe the login form is actually being validated by the login action
        // (but keep in mind we don't want to show invalid username vs invalid password or
        // anything like that, it should just say "login failed" without any additional info)
        //$csrf = new Element\Csrf('csrf');
        //$csrf->getValidator()->setTimeout($options->getLoginFormTimeout());
        //$this->add($csrf);

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Login')
            ->setAttributes(array(
                'type'  => 'submit',
                'class' => 'btn btn-success'
            ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

        $this->getEventManager()->trigger('init', $this);
    }

    /**
     * Set Authentication-related Options
     *
     * @param  AuthenticationOptionsInterface $authOptions
     * @return Login
     */
    public function setAuthenticationOptions(AuthenticationOptionsInterface $authOptions)
    {
        $this->authOptions = $authOptions;

        return $this;
    }

    /**
     * Get Authentication-related Options
     *
     * @return AuthenticationOptionsInterface
     */
    public function getAuthenticationOptions()
    {
        return $this->authOptions;
    }
}
