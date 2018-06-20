<?php

namespace PlaygroundUser\Form;

use Zend\InputFilter\InputFilter;
use PlaygroundUser\Options\ForgotOptionsInterface;
use Zend\Mvc\I18n\Translator;

class ResetFilter extends InputFilter
{
    public function __construct(ForgotOptionsInterface $options, Translator $translator)
    {
        $this->add(array(
            'name'       => 'newCredential',
            'required'   => true,
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'min' => 6,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_SHORT => $translator->translate(
                                'Your password contains less than 6 characters',
                                'playgrounduser'
                            ),
                        ),
                    ),
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $translator->translate(
                                'Enter your new password',
                                'playgrounduser'
                            ),
                        ),
                    ),
                ),
            ),
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
        ));

        $this->add(array(
            'name'       => 'newCredentialVerify',
            'required'   => true,
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'min' => 6,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_SHORT => $translator->translate(
                                'Your password contains less than 6 characters',
                                'playgrounduser'
                            ),
                        ),
                    ),
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $translator->translate(
                                'Confirm your password',
                                'playgrounduser'
                            ),
                        ),
                    ),
                ),
                array(
                    'name' => 'identical',
                    'options' => array(
                        'token' => 'newCredential',
                        'messages' => array(
                            \Zend\Validator\Identical::NOT_SAME => $translator->translate(
                                'Your passwords are different',
                                'playgrounduser'
                            ),
                        ),
                    )
                ),
            ),
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
        ));
    }
}
