<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Cron;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CronFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Cron($container);

        return $service;
    }
}
