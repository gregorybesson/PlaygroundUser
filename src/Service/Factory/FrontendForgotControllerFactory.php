<?php

namespace PlaygroundUser\Service\Factory;

use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use PlaygroundUser\Controller\Frontend\ForgotController;

class FrontendForgotControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new ForgotController($container);

        return $controller;
    }
}
