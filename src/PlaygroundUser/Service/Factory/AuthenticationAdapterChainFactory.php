<?php
namespace PlaygroundUser\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Authentication\Adapter\AdapterChainServiceFactory;

class AuthenticationAdapterChainFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {

        $factory = new AdapterChainServiceFactory();
        $chain = $factory->createService($services);
        $adapter = $services->get('playgrounduser_authentication_hybridauth');
        $chain->getEventManager()->attach('authenticate', array($adapter, 'authenticate'), 1000);
        $adapter = $services->get('playgrounduser_authentication_emailvalidation');
        $chain->getEventManager()->attach('authenticate', array($adapter, 'authenticate'), 1100);

        return $chain;
    }
}
