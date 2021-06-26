<?php

namespace PlaygroundUser\Form\Admin;

use PlaygroundUser\Options\UserCreateOptionsInterface;
use ZfcUser\Options\RegistrationOptionsInterface;
use ZfcUser\Form\Register as Register;
use Laminas\Mvc\I18n\Translator;

class User extends Register
{
    /**
     * @var userRolesMapper
     */
    protected $userRolesMapper;
    /**
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    public function __construct($name, UserCreateOptionsInterface $createOptions, RegistrationOptionsInterface $registerOptions, Translator $translator, $serviceManager)
    {
        $this->setCreateOptions($createOptions);
        $this->setServiceManager($serviceManager);
        parent::__construct($name, $registerOptions);

        $availableRoles = $this->getUserRolesMapper()->getRoles();
        $rolesSelect = array();
        foreach ($availableRoles as $id => $role) {
            $rolesSelect[$role->getRoleId()] = array(
                'value' => $role->getRoleId(),
                'label' => $role->getRoleId(),
                'selected' => false,
            );
        }
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => 0
            ),
        ));

        // create a password automaticaly
        if ($createOptions->getCreateUserAutoPassword()) {
            $this->remove('password');
            $this->remove('passwordVerify');
        }

        $this->get('username')->setLabel($translator->translate('Username', 'playgrounduser'));
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
            'name' => 'title',
            'type' => 'Laminas\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Title', 'playgrounduser'),
                'value_options' => array(
                    'M'  => $translator->translate('Mister', 'playgrounduser'),
                    'Me' => $translator->translate('Miss', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'roleId',
            'attributes' =>  array(
                'id' => 'roleId',
                'options' => $rolesSelect,
            ),
            'options' => array(
                'empty_option' => $translator->translate('Select a role', 'playgrounduser'),
                'label' => $translator->translate('Roles', 'playgrounduser'),
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
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'country',
            'options' => array(
                'empty_option' => $translator->translate('Select your country', 'playgrounduser'),
                'value_options' => $countries_label,
                'label' => $translator->translate('Country', 'playgrounduser')
            )
        ));

        $this->add(array(
            'name' => 'telephone',
            'options' => array(
                'label' => $translator->translate('Telephone', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Telephone', 'playgrounduser'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\DateTime',
            'name' => 'dob',
            'options' => array(
                'label' => $translator->translate('Date of birth', 'playgrounduser'),
                'format' => 'd/m/Y'
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Date of birth', 'playgrounduser'),
                'class'=> 'date-birth'
            )
        ));

        $this->add(array(
            'name' => 'optin',
            'type' => 'Laminas\Form\Element\Checkbox',
            'options' => array(
                'label' => $translator->translate('Newsletter', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Yes', 'playgrounduser'),
                    '0'  => $translator->translate('No', 'playgrounduser'),
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
                    '0'  => $translator->translate('No', 'playgrounduser'),
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
                    '0'  => $translator->translate('No', 'playgrounduser'),
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
                    '0'  => $translator->translate('No', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'registrationSource',
            'options' => array(
                'label' => $translator->translate('Registration source', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Registration source', 'playgrounduser'),
            ),
        ));

        /*foreach ($this->getCreateOptions()->getCreateFormElements() as $name => $element) {
            $this->add(array(
                'name' => $element,
                'options' => array(
                    'label' => $name,
                ),
                'attributes' => array(
                    'type' => 'text'
                ),
            ));
        }*/

        $this->get('submit')->setLabel('Create');
    }

    public function getUserRolesMapper()
    {
        if (null === $this->userRolesMapper) {
            $this->userRolesMapper = $this->getServiceManager()->get('BjyAuthorize\Provider\Role\ObjectRepositoryProvider');
        }

        return $this->userRolesMapper;
    }

    public function setUserRolesMapper($userRolesMapper)
    {
        $this->userRolesMapper = $userRolesMapper;

        return $this;
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
