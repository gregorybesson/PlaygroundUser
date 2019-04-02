<?php

namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\EmailValidation as EmailValidationAdapter;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class EmailValidationAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = new EmailValidationAdapter($container);
        $adapter->setServiceManager($container);

        return $adapter;
    }
}
