<?php

namespace PlaygroundUser\Form;

use ZfcUser\Form\Register as Register;
use PlaygroundUser\Options\UserCreateOptionsInterface;
use Zend\Mvc\I18n\Translator;

class ChangeInfo extends Register
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

        $this->setAttribute('enctype', 'multipart/form-data');
        $this->remove('password');
        $this->remove('passwordVerify');
        $this->remove('username');

        $this->add(array(
                'name' => 'id',
                'attributes' => array(
                        'type' => 'hidden',
                        'value' => 0
                ),
        ));

        $this->add(array(
                'name' => 'username',
                'options' => array(
                        'label' => $translator->translate('Username', 'playgrounduser'),
                ),
                'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate('Username', 'playgrounduser'),
                ),
        ));

        $this->add(array(
            'name' => 'lastname',
            'options' => array(
                'label' => $translator->translate('Last Name', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Last Name', 'playgrounduser'),
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
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Title', 'playgrounduser'),
                'value_options' => array(
                    'M'  => $translator->translate('Mister', 'playgrounduser'),
                    'Me' => $translator->translate('Miss', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
            'attributes' => array(
              'type'  => 'file',
            ),
            'options' => array(
              'label' => $translator->translate('Avatar', 'playgrounduser'),
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
              'class' => 'number zipcodefr',
              'minlength' => 5,
              'maxlength' => 10,
            ),
        ));

        $this->add(
            array(
                'name' => 'city',
                'options' => array(
                    'label' => $translator->translate('City', 'playgrounduser'),
                    ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('City', 'playgrounduser'),
                ),
            )
        );

        $cs = $this->getServiceManager()->get('playgroundcore_country_service');
        $countries = $cs->getAllCountries();
        $countries_label = array();
        foreach ($countries as $key => $name) {
            $countries_label[$key] = $translator->translate($name, 'playgrounduser');
        }
        asort($countries_label);
        $this->add(
            array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'country',
                'options' => array(
                    'empty_option' => $translator->translate('Select your country', 'playgrounduser'),
                    'value_options' => $countries_label,
                    'label' => $translator->translate('Country', 'playgrounduser')
                )
            )
        );

        $this->add(
            array(
                'name' => 'telephone',
                'options' => array(
                    'label' => $translator->translate('Telephone', 'playgrounduser'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Telephone', 'playgrounduser'),
                    'class' => 'number phonefr',
                    'maxlength' => '10',
                ),
            )
        );

        $this->add(
            array(
                'type' => 'Zend\Form\Element\DateTime',
                'name' => 'dob',
                'options' => array(
                    'label' => $translator->translate('Date of birth', 'playgrounduser'),
                    'format' => 'd/m/Y'
                ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Date of birth', 'playgrounduser'),
                    'class'=> 'date'
                )
            )
        );

        $this->add(
            array(
                'name' => 'optin',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => $translator->translate('Newsletter', 'playgrounduser'),
                    'value_options' => array(
                        '1'  => $translator->translate('Yes', 'playgrounduser'),
                        '0' => $translator->translate('No', 'playgrounduser'),
                    ),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'optin2',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => $translator->translate('optin 2', 'playgrounduser'),
                    'value_options' => array(
                        '1'  => $translator->translate('Yes', 'playgrounduser'),
                        '0' => $translator->translate('No', 'playgrounduser'),
                    ),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'optinPartner',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => $translator->translate('Partners Newsletter', 'playgrounduser'),
                    'value_options' => array(
                        '1'  => $translator->translate('Yes', 'playgrounduser'),
                        '0' => $translator->translate('No', 'playgrounduser'),
                    ),
                ),
            )
        );

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