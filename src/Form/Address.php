<?php

namespace PlaygroundUser\Form;

use ZfcUser\Form\Register as Register;
use PlaygroundUser\Options\UserCreateOptionsInterface;
use Zend\Mvc\I18n\Translator;

class Address extends Register
{
    /**
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    public function __construct($name, UserCreateOptionsInterface $createOptions, Translator $translator, $serviceManager)
    {
        $this->setCreateOptions($createOptions);
        $this->setServiceManager($serviceManager);
        parent::__construct($name, $createOptions);

        $this->remove('password');
        $this->remove('passwordVerify');
        $this->remove('username');
        $this->remove('dob');

        $this->add(array(
            'name' => 'lastname',
            'options' => array(
                'label' => $translator->translate('Last Name', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Last Name', 'playgrounduser'),
                'class' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'firstname',
            'options' => array(
                'label' => $translator->translate('First Name', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('First Name', 'playgrounduser'),
                'class' => 'required',
            ),
        ));

        $this->add(array(
                'name' => 'address',
                'options' => array(
                        'label' => $translator->translate('Address', 'playgrounduser'),
                ),
                'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate('Address', 'playgrounduser'),
                        'class' => 'required',
                ),
        ));

        $this->add(array(
                'name' => 'address2',
                'options' => array(
                        'label' => $translator->translate('Address 2', 'playgrounduser'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Address 2', 'playgrounduser'),
                ),
        ));

        $this->add(array(
            'name' => 'postalCode',
            'options' => array(
                    'label' => $translator->translate('Postal Code', 'playgrounduser'),
            ),
            'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Postal Code', 'playgrounduser'),
                    'class' => 'number required',
                    'minlength' => 5,
                    'maxlength' => 10,
            ),
        ));

        $this->add(array(
                'name' => 'city',
                'options' => array(
                        'label' => $translator->translate('City', 'playgrounduser'),
                ),
                'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate('City', 'playgrounduser'),
                        'class' => 'required',
                ),
        ));

        $cs = $this->getServiceManager()->get('playgroundcore_country_service');
        $countries = $cs->getAllCountries();
        $countries_label = array();
        foreach ($countries as $key => $name) {
            $countries_label[$key] = $translator->translate($name, 'playgrounduser');
        }
        asort($countries_label);
        
        $this->add(array(
               'type' => 'Zend\Form\Element\Select',
               'name' => 'country',
               'options' => array(
                   'empty_option' => $translator->translate('Select your country', 'playgrounduser'),
                   'value_options' => $countries_label,
                   'label' => $translator->translate('Country', 'playgrounduser')
               )
        ));

        $this->get('submit')->setLabel('Create');
    }

    public function setCreateOptions(UserCreateOptionsInterface $createOptionsOptions)
    {
        $this->createOptions = $createOptionsOptions;

        return $this;
    }

    public function getCreateOptions()
    {
        return $this->createOptions;
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
