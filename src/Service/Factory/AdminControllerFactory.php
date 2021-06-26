<?php

namespace PlaygroundUser\Service\Factory;

use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use PlaygroundUser\Controller\Admin\AdminController;

class AdminControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new AdminController($container);

        return $controller;
    }
}
