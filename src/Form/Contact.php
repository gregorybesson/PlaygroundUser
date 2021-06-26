<?php

namespace PlaygroundUser\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use ZfcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class Contact extends ProvidesEventsForm
{

    protected $serviceManager;

    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);
        $this->setServiceManager($sm);

        $this->add(array(
            'name' => 'lastname',
            'options' => array(
                'label' => $translator->translate('Your last name', 'playgroundcore'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Your last name', 'playgroundcore'),
                'class' => 'large-input required',
            ),
        ));

        $this->add(array(
            'name' => 'firstname',
            'options' => array(
                'label' => $translator->translate('Your first name', 'playgroundcore'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Your first name', 'playgroundcore'),
                'class' => 'large-input required',
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => $translator->translate('Your email', 'playgroundcore'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Your email', 'playgroundcore'),
                'class' => 'large-input required email',
            ),
        ));

        $this->add(array(
                'type' => 'Laminas\Form\Element\Select',
                'name' => 'object',
                'options' => array(
                    'label' => $translator->translate('Objet', 'playgroundcore'),
                    'value_options' => array(
                                                'technical-pb'  =>  $translator->translate('I have a technical problem', 'playgroundcore'),
                                                'games-questions'   =>  $translator->translate('I have a question about games', 'playgroundcore'),
                                                'no-invit'  =>  $translator->translate('I have not received my lot or my invitation', 'playgroundcore'),
                                                'comment'   =>  $translator->translate('I have a comment or suggestion', 'playgroundcore'),
                                                'other'     =>  $translator->translate('Other', 'playgroundcore'),
                                            ),
                    'empty_option' => $translator->translate('Select', 'playgroundcore'),
                ),
                'attributes' => array(
                    'class' => 'required',
                ),
        ));

        $this->add(array(
                'type' => 'Laminas\Form\Element\Textarea',
                'name' => 'message',
                'options' => array(
                    'label' => $translator->translate('Your question', 'playgroundcore'),
                ),
                'attributes' => array(
                    'placeholder' => $translator->translate('Your question', 'playgroundcore'),
                    'class' => 'required',
                ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Send', 'playgroundcore'))
            ->setAttributes(array(
            'type' => 'submit',
            'class'=> 'btn btn-success'
            ));

        $this->add($submitElement, array(
            //'priority' => - 100
        ));
    }

     /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
