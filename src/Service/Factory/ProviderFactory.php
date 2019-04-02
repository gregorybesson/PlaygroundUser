<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Provider;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ProviderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Provider($container);

        return $service;
    }
}
