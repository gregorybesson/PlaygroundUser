<?php
namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Service\Team;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TeamFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Service\Team
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Team($locator);

        return $service;
    }
}
