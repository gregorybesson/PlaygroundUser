<?php

namespace PlaygroundUser\Service\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Controller\RedirectCallback;
use PlaygroundUser\Controller\Frontend\UserController;

class UserControllerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $controllerManager
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $locator)
    {
        $parentLocator = $locator->getServiceLocator();

        /* @var UserController $controller */
        $controller = new UserController($parentLocator);

        return $controller;
    }
}
