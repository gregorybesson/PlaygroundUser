<?php

namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\HybridAuth as HybridAuthAdapter;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */
class HybridAuthAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        try {
            $hybridAuth = $container->get('HybridAuth');
        } catch (\Exception $e) {
            // In some cases (ie. FB registration, the user refuses the options)
            $hybridAuth = null;
        }

        $moduleOptions = $container->get('playgrounduser_module_options');
        $zfcUserOptions = $container->get('zfcuser_module_options');

        $mapper = $container->get('playgrounduser_userprovider_mapper');
        $zfcUserMapper = $container->get('zfcuser_user_mapper');

        $adapter = new HybridAuthAdapter();
        $adapter->setHybridAuth($hybridAuth);
        $adapter->setOptions($moduleOptions);
        $adapter->setZfcUserOptions($zfcUserOptions);
        $adapter->setMapper($mapper);
        $adapter->setZfcUserMapper($zfcUserMapper);
        $adapter->setServiceManager($container);

        return $adapter;
    }
}
