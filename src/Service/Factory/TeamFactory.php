<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Team;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TeamFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Team($container);

        return $service;
    }
}
