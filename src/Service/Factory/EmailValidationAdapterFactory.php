<?php

namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\EmailValidation as EmailValidationAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EmailValidationAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $locator)
    {
        $adapter = new EmailValidationAdapter($locator);

        return $adapter;
    }
}
