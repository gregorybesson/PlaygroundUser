<?php
namespace PlaygroundUser\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcUser\Options\RegistrationOptionsInterface;
use ZfcUser\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;

class Newsletter extends ProvidesEventsForm
{

    /**
     *
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    public function __construct($name, RegistrationOptionsInterface $registerOptions, Translator $translator)
    {
        parent::__construct($name);

        $this->add(array(
            'name' => 'optin',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => $translator->translate('Newsletter', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Yes', 'playgrounduser'),
                    '0' => $translator->translate('No', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'optin2',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => $translator->translate('Optin 2', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Yes', 'playgrounduser'),
                    '0' => $translator->translate('No', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'optinPartner',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => $translator->translate('Partners Newsletter', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Yes', 'playgrounduser'),
                    '0' => $translator->translate('No', 'playgrounduser'),
                ),
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Validate', 'playgrounduser'))
        ->setAttributes(array(
                'type' => 'submit',
                'class' => 'btn btn-success'
        ));

        $this->add($submitElement, array(
                'priority' => - 100
        ));
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
