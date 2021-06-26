<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\RememberMe;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RememberMeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new RememberMe($container);

        return $service;
    }
}
