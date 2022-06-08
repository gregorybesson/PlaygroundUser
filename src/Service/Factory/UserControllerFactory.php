<?php

namespace PlaygroundUser\Service\Factory;

use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use LmcUser\Controller\RedirectCallback;
use PlaygroundUser\Controller\Frontend\UserController;

class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var UserController $controller */
        $controller = new UserController($container);

        return $controller;
    }
}
