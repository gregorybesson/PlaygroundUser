<?php

namespace PlaygroundUser\Service\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundUser\Controller\Frontend\ForgotController;

class FrontendForgotControllerFactory implements FactoryInterface
{

    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Controller\Frontend\ForgotController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new ForgotController($locator);

        return $controller;
    }
}
