<?php
namespace PlaygroundUser\Form\Admin;

use Zend\Form\Element;
use Zend\Form\Form;
use PlaygroundUser\Options\UserCreateOptionsInterface;
use ZfcBase\Form\ProvidesEventsForm;
use ZfcUser\Options\RegistrationOptionsInterface;
use Zend\Mvc\I18n\Translator;

class Role extends ProvidesEventsForm
{

    /**
     *
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    /**
     * @var userRolesMapper
     */
    protected $userRolesMapper;

    public function __construct($name = null, RegistrationOptionsInterface $registerOptions, Translator $translator, $serviceManager)
    {
        $this->setServiceManager($serviceManager);
        parent::__construct($name);


        $availableRoles = $this->getUserRolesMapper()->getRoles();
        $rolesSelect = array();
        foreach ($availableRoles as $id => $role) {
            $rolesSelect[$role->getRoleId()] = array(
                'value' => $role->getRoleId(),
                'label' => $role->getRoleId(),
                'selected' => false,
            );
        }

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => 0
            ),
        ));

        $this->add(array(
            'name' => 'roleId',
            'options' => array(
                'label' => $translator->translate('Role Name', 'playgrounduser')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'large-input required',
                'placeholder' => $translator->translate('Role name', 'playgrounduser')
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'parentRoleId',
            'attributes' =>  array(
                'id' => 'parentRoleId',
                'options' => $rolesSelect,
            ),
            'options' => array(
                'empty_option' => $translator->translate('Select a parent role', 'playgrounduser'),
                'label' => $translator->translate('Parent role', 'playgrounduser'),
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

    public function getUserRolesMapper()
    {
        if (null === $this->userRolesMapper) {
            $this->userRolesMapper = $this->getServiceManager()->get('BjyAuthorize\Provider\Role\DoctrineEntity');
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
