<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\Cookie;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CookieAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Cookie($container);

        return $service;
    }
}