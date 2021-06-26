<?php

namespace PlaygroundUser\Form;

use Laminas\InputFilter\InputFilter;
use ZfcUser\Options\AuthenticationOptionsInterface;

class LoginFilter extends \ZfcUser\Form\LoginFilter
{
    public function __construct(AuthenticationOptionsInterface $options)
    {
        $this->add(
            array(
                'name'       => 'remember_me',
                'required'   => false,
                'allowEmpty' => true,
            )
        );

        parent::__construct($options);
    }
}
