<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\Cookie;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CookieAdapterFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Authentication\Adapter\Cookie
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Cookie($locator);

        return $service;
    }
}
