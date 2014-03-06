<?php

namespace PlaygroundUser\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;

class TokenSecure extends EventProvider implements ServiceManagerAwareInterface {
    
   protected $options;
    
    public function generateValue($userId, $email)
    {
        $secretKey = $this->getOptions()->getSecretKey();
        $ticket = $this->getTicket($userId, $email);
        return $ticket.":".sha1($ticket.':'.$secretKey);
    }
    
    public function checkToken($token)
    {
        $secretKey = $this->getOptions()->getSecretKey();
        if (strpos($token, ':') !== false) {
            list($id, $signature) = explode(':', $token, 2);
            if ($signature == sha1($id.':'.$secretKey)) {
                return $id;
            }

            return false;
        }   

        return true;
    }
    
    public function getTicket($userId, $email)
    {
        $sha1 = md5($userId.$email);
        return $sha1;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }

    public function setOptions(\ZfcUser\Options\UserServiceOptionsInterface $options)
    {
        $this->options = $options;

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
     * @param  ServiceManager $locator
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}