<?php

namespace PlaygroundUser\Form;

use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use PlaygroundUser\Options\ForgotOptionsInterface;
use Laminas\Mvc\I18n\Translator;

class Forgot extends ProvidesEventsForm
{
    /**
     * @var AuthenticationOptionsInterface
     */
    protected $forgotOptions;

    public function __construct($name, ForgotOptionsInterface $options, Translator $translator)
    {
        $this->setForgotOptions($options);
        parent::__construct($name);

        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => 'E-Mail',
            ),
            'attributes' => array(
                'type' => 'email',
                'class' => 'forgot-email required',
                'placeholder' => $translator->translate('Your email adress', 'playgrounduser')
            )
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel($translator->translate('Request new password', 'playgrounduser'))
            ->setAttributes(array(
                'type'  => 'submit',
                'class' => 'btn btn-success',
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
