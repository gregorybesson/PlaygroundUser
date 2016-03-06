<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\RememberMe;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RememberMeFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Service\RememberMe
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new RememberMe($locator);

        return $service;
    }
}
