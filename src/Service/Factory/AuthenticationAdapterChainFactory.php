<?php
namespace PlaygroundUser\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use ZfcUser\Authentication\Adapter\AdapterChainServiceFactory;

class AuthenticationAdapterChainFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $factory = new AdapterChainServiceFactory();
        $chain = $factory->createService($container);
        $adapter = $container->get('playgrounduser_authentication_hybridauth');
        $chain->getEventManager()->attach('authenticate', array($adapter, 'authenticate'), 90);
        $adapter = $container->get('playgrounduser_authentication_emailvalidation');
        $chain->getEventManager()->attach('authenticate', array($adapter, 'authenticate'), 100);

        return $chain;
    }
}
