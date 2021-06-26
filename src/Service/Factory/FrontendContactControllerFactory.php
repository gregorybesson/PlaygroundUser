<?php

namespace PlaygroundUser\Service\Factory;

use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use PlaygroundUser\Controller\Frontend\ContactController;

class FrontendContactControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new ContactController($container);

        return $controller;
    }
}
