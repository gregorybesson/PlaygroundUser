<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\User;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class UserFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new User($container);

        return $service;
    }
}