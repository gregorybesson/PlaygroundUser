<?php
namespace PlaygroundUser\Form;

use Zend\Form\Form;
use PlaygroundUser\Options\UserCreateOptionsInterface;
use ZfcUser\Options\RegistrationOptionsInterface;
use Zend\I18n\Translator\Translator;

class Register extends \ZfcUser\Form\Register
{

    /**
     *
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    public function __construct ($name = null, RegistrationOptionsInterface $registerOptions, Translator $translator, $serviceManager)
    {
        $this->setServiceManager($serviceManager);
        parent::__construct($name, $registerOptions);

        if($this->has('username')){
        	$this->get('username')
        	->setLabel($translator->translate('Username', 'playgrounduser'))
        	->setAttributes(array('placeholder' => 'Your username'));
        }
        
        $this->get('email')
            ->setLabel($translator->translate('Your Email', 'playgrounduser'))
            ->setAttributes(array('type' => 'email', 'class' => 'large-input required email', 'placeholder' => $translator->translate('Your Email', 'playgrounduser')));
        $this->get('password')
            ->setLabel($translator->translate('Your Password', 'playgrounduser'))
            ->setAttributes(array('id' => 'password', 'class' => 'large-input required security', 'placeholder' => $translator->translate('Your password', 'playgrounduser')));
        $this->get('passwordVerify')
            ->setLabel($translator->translate('Confirm your Password', 'playgrounduser'))
            ->setAttributes(array('id' => 'passwordVerify', 'class' => 'large-input required', 'placeholder' => $translator->translate('Confirm your Password', 'playgrounduser')));

        $this->add(array(
            'name' => 'firstname',
            'options' => array(
                'label' => $translator->translate('First Name', 'playgrounduser')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'large-input required',
                'placeholder' => $translator->translate('Your first name', 'playgrounduser')
            )
        ));

        $this->add(array(
            'name' => 'lastname',
            'options' => array(
                'label' => $translator->translate('Last Name', 'playgrounduser')
            ),
            'attributes' => array(
                'type' => 'text',
                'class'=> 'large-input required',
                'placeholder' => $translator->translate('Your last name', 'playgrounduser')
            )
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Title', 'playgrounduser'),
                'value_options' => array(
                    'M' => $translator->translate('Mister', 'playgrounduser'),
                    'Me' => $translator->translate('Miss', 'playgrounduser')
                )
            ),
            'attributes' => array(
                'class'=> 'required',
            ),
        ));

        $this->add(array(
            'name' => 'postal_code',
            'options' => array(
                'label' => $translator->translate('Postal Code', 'playgrounduser')
            ),
            'attributes' => array(
                'id' => 'postalcode',
                'type' => 'text',
                'class'=> 'medium-input required number',
                'maxlength' => 5,
                'placeholder' => $translator->translate('Your zip code', 'playgrounduser')
            )
        ));

        $this->add(array(
			'type' => 'Zend\Form\Element\DateTime',
			'name' => 'dob',
			'options' => array(
				'label' => $translator->translate('Date of birth', 'playgrounduser'),
				'format' => 'd/m/Y'
			),
			'attributes' => array(
				'type' => 'text',
				'placeholder' => $translator->translate('Date of birth', 'playgrounduser'),
				'class'=> 'date required'
			)
        ));

        $this->add(array(
            'name' => 'optin',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Newsletter', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Oui', 'playgrounduser'),
                    '0' => $translator->translate('Non', 'playgrounduser'),
                ),
            ),
            'attributes' => array(
                'class'=> 'required',
            ),
        ));

        $this->add(array(
            'name' => 'optinPartner',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Newsletter des partenaires', 'playgrounduser'),
                'value_options' => array(
                    '1'  => $translator->translate('Oui', 'playgrounduser'),
                    '0' => $translator->translate('Non', 'playgrounduser'),
                ),
            ),
            'attributes' => array(
                'class'=> 'required',
            ),
        ));

        $this->get('submit')
            ->setLabel($translator->translate('Create an account and participate', 'playgrounduser'))
            ->setAttributes(array(
                'class' => 'btn btn-success'
            ));
    }

    public function setCreateOptions (UserCreateOptionsInterface $createOptionsOptions)
    {
        $this->createOptions = $createOptionsOptions;

        return $this;
    }

    public function getCreateOptions ()
    {
        return $this->createOptions;
    }

    public function setServiceManager ($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager ()
    {
        return $this->serviceManager;
    }
}
