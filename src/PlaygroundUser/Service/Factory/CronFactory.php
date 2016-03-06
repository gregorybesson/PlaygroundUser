<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Cron;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CronFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Service\Cron
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Cron($locator);

        return $service;
    }
}
