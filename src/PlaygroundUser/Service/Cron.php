<?php

namespace PlaygroundUser\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundUser\Options\ModuleOptions;

class Cron extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    /**
     * @var UserMapperInterface
     */
    protected $userMapper;

    public static function disableUser($interval = 0)
    {
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $userService = $sm->get('playgrounduser_cron_service');
        $options = $sm->get('playgrounduser_module_options');

        $userService->disablePendingAccounts($interval);
    }

    public function disablePendingAccounts($interval = 0)
    {
        $period = new \DateTime('now');
        $interval = 'P'.$interval.'D';
        $period->sub(new \DateInterval($interval));
        $period = $period->format('Y-m-d') . ' 0:0:0';

        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();

        // Users with disable pending since n days
        $query = $em->createQuery('SELECT u FROM ' . $userClass . ' u WHERE (u.updated_at <= :date AND u.state = 2)');
        $query->setParameter('date', $period);
        $usersToDisable = $query->getResult();

        foreach ($usersToDisable as $user) {
            $user->setState(0);
            $this->getUserMapper()->update($user);
        }
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('zfcuser_user_mapper');
        }

        return $this->userMapper;
    }

    /**
     * setUserMapper
     *
     * @param  UserMapperInterface $userMapper
     * @return User
     */
    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;

        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
