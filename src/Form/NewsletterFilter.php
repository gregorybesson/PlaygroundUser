<?php

namespace PlaygroundUser\Form;

use Zend\InputFilter\InputFilter;
use ZfcUser\Options\AuthenticationOptionsInterface;

class NewsletterFilter extends InputFilter
{
    public function __construct(AuthenticationOptionsInterface $options)
    {
        $this->add(array(
            'name'       => 'optin',
            'required'   => false,
            'filters' => array(
                array('name'    => 'Int'),
            ),
        ));

        $this->add(array(
            'name'       => 'optin2',
            'required'   => false,
            'filters' => array(
                array('name'    => 'Int'),
            ),
        ));

        $this->add(array(
            'name'       => 'optin3',
            'required'   => false,
            'filters' => array(
                array('name'    => 'Int'),
            ),
        ));

        $this->add(array(
            'name'       => 'optinPartner',
            'required'   => false,
            'filters' => array(
                array('name'    => 'Int'),
            ),
        ));
    }
}
