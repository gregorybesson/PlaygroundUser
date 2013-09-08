<?php

namespace PlaygroundUser\Service\Factory;

use PlaygroundUser\Authentication\Adapter\HybridAuth as HybridAuthAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */
class HybridAuthAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
     	try{
    		$config = $services->get('SocialConfig');
    		$hybridAuth = new \Hybrid_Auth($config);
        }catch(\Exception $e){
			// In some cases (ie. FB registration, the user refuses the options)
        	$hybridAuth = null;
        }

        $moduleOptions = $services->get('playgrounduser_module_options');
        $zfcUserOptions = $services->get('zfcuser_module_options');

        $mapper = $services->get('playgrounduser_userprovider_mapper');
        $zfcUserMapper = $services->get('zfcuser_user_mapper');

        $adapter = new HybridAuthAdapter();
        $adapter->setHybridAuth($hybridAuth);
        $adapter->setOptions($moduleOptions);
        $adapter->setZfcUserOptions($zfcUserOptions);
        $adapter->setMapper($mapper);
        $adapter->setZfcUserMapper($zfcUserMapper);

        return $adapter;
    }
}
