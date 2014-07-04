<?php

namespace PlaygroundUser\Form;

use Zend\Form\Form;
use ZfcBase\Form\ProvidesEventsForm;
use ZfcUser\Options\AuthenticationOptionsInterface;
use ZfcUser\Module as ZfcUser;
use Zend\Mvc\I18n\Translator;

class BlockAccount extends ProvidesEventsForm
{
    /**
     * @var AuthenticationOptionsInterface
     */
    protected $authOptions;

    public function __construct($name = null, AuthenticationOptionsInterface $options, Translator $translator)
    {
        $this->setAuthenticationOptions($options);
        parent::__construct($name);

        $this->add(array(
                'name' => 'activate',
                'options' => array(
                    'label' => '',
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'value' => 0
                ),
        ));

        $this->add(array(
            'name' => 'identity',
            'options' => array(
                'label' => '',
            ),
            'attributes' => array(
                'type' => 'hidden'
            ),
        ));

        $this->add(array(
            'name' => 'credential',
            'options' => array(
                'label' => $translator->translate('Current Password', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'password',
                'class' => 'large-input'
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'class' => 'btn btn-success',
                'type'  => 'button',
                'value' => $translator->translate('Delete my account', 'playgrounduser'),
                'id' => 'del-confirm',
            ),
        ));

        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'class' => 'btn btn-cancel',
                'type'  => 'button',
                'value' => $translator->translate('Cancel', 'playgrounduser'),
                'id' => 'del-cancel',
            ),
        ));

        $this->add(array(
            'name' => 'confirm_submit',
            'attributes' => array(
                'class' => 'btn btn-success',
                'type'  => 'submit',
                'value' => $translator->translate('Confirm the deletion of my account', 'playgrounduser'),
                'id' => 'del-input',
            ),
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
