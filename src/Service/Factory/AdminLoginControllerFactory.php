<?php

namespace PlaygroundUser\Service\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use ZfcUser\Controller\RedirectCallback;
use PlaygroundUser\Controller\Admin\LoginController;

class AdminLoginControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var UserController $controller */
        $controller = new LoginController($container);

        return $controller;
    }
}
