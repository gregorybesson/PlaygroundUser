<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\User;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class UserFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new User($container);

        return $service;
    }
}
