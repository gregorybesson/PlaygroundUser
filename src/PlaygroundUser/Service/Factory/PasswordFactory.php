<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Password;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PasswordFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Service\Password
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Password($locator);

        return $service;
    }
}
