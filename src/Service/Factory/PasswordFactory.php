<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Password;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PasswordFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Password($container);

        return $service;
    }
}
