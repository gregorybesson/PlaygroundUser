<?php
namespace PlaygroundUser\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use LmcUser\Options\RegistrationOptionsInterface;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;

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
            'type' => 'Laminas\Form\Element\Checkbox',
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
            'type' => 'Laminas\Form\Element\Checkbox',
            'options' => array(
                'label' => $translator->translate('Optin 2', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Yes', 'playgrounduser'),
                    '0' => $translator->translate('No', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'optin3',
            'type' => 'Laminas\Form\Element\Checkbox',
            'options' => array(
                'label' => $translator->translate('Optin 3', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Yes', 'playgrounduser'),
                    '0' => $translator->translate('No', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'optinPartner',
            'type' => 'Laminas\Form\Element\Checkbox',
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
