<?php

namespace PlaygroundUser\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use PlaygroundUser\Options\ForgotOptionsInterface;
use Laminas\Mvc\I18n\Translator;

class Reset extends ProvidesEventsForm
{
    /**
     * @var ForgotOptionsInterface
     */
    protected $forgotOptions;

    public function __construct($name, ForgotOptionsInterface $forgotOptions, Translator $translator)
    {
        $this->setForgotOptions($forgotOptions);
        parent::__construct($name);

        $this->add(array(
            'name' => 'newCredential',
            'options' => array(
                'label' => $translator->translate('New Password', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'password',
            ),
        ));

        $this->add(array(
            'name' => 'newCredentialVerify',
            'options' => array(
                'label' => $translator->translate('Verify New Password', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'password',
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel($translator->translate('Set new password', 'playgrounduser'))
            ->setAttributes(array(
                'type'  => 'submit',
                'class' => 'btn btn-success'
            ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

        $this->getEventManager()->trigger('init', $this);
    }

    public function setForgotOptions(ForgotOptionsInterface $forgotOptions)
    {
        $this->forgotOptions = $forgotOptions;

        return $this;
    }

    public function getForgotOptions()
    {
        return $this->forgotOptions;
    }
}
