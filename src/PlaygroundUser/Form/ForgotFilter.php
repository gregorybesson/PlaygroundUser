<?php

namespace PlaygroundUser\Form;

use Zend\InputFilter\InputFilter;
use PlaygroundUser\Options\ForgotOptionsInterface;

class ForgotFilter extends InputFilter
{
    /**
     * @var ForgotOptionsInterface
     */
    protected $options;

    public function __construct( ForgotOptionsInterface $options)
    {
        $this->setOptions($options);

        $this->add(array(
            'name'       => 'email',
            'required'   => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress'
                ),
            ),
        ));
    }

    /**
     * set options
     *
     * @param RegistrationOptionsInterface $options
     */
    public function setOptions(ForgotOptionsInterface $options)
    {
        $this->options = $options;
    }

    /**
     * get options
     *
     * @return RegistrationOptionsInterface
     */
    public function getOptions()
    {
        return $this->options;
    }

}
