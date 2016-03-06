<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\User;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Service\User
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new User($locator);

        return $service;
    }
}
