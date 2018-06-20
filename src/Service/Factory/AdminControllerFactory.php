<?php

namespace PlaygroundUser\Service\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundUser\Controller\Admin\AdminController;

class AdminControllerFactory implements FactoryInterface
{

    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundUser\Controller\Admin\AdminController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new AdminController($locator);

        return $controller;
    }
}
