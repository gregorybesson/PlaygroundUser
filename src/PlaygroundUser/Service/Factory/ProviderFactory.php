<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Provider;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProviderFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Service\Provider
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Provider($locator);

        return $service;
    }
}
