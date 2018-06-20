<?php

namespace PlaygroundUser\Service\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundUser\Controller\Frontend\ContactController;

class FrontendContactControllerFactory implements FactoryInterface
{

    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Controller\Frontend\ContactController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new ContactController($locator);

        return $controller;
    }
}
