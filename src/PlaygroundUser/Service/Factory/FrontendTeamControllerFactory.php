<?php

namespace PlaygroundUser\Service\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundUser\Controller\Frontend\TeamController;

class FrontendTeamControllerFactory implements FactoryInterface
{

    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Controller\Frontend\TeamController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new TeamController($locator);

        return $controller;
    }
}
