<?php

namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\HybridAuth as HybridAuthAdapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */
class HybridAuthAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        try {
            $hybridAuth = $container->get('HybridAuth');
        } catch (\Exception $e) {
            // In some cases (ie. FB registration, the user refuses the options)
            $hybridAuth = null;
        }

        $moduleOptions = $container->get('playgrounduser_module_options');
        $lmcuserOptions = $container->get('lmcuser_module_options');

        $mapper = $container->get('playgrounduser_userprovider_mapper');
        $lmcuserMapper = $container->get('lmcuser_user_mapper');

        $adapter = new HybridAuthAdapter();
        $adapter->setHybridAuth($hybridAuth);
        $adapter->setOptions($moduleOptions);
        $adapter->setLmcUserOptions($lmcuserOptions);
        $adapter->setMapper($mapper);
        $adapter->setLmcUserMapper($lmcuserMapper);
        $adapter->setServiceManager($container);

        return $adapter;
    }
}
