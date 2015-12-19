<?php

namespace PlaygroundUser\Authentication\Adapter;

use ZfcUser\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result as AuthenticationResult;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class EmailValidation extends AbstractAdapter implements ServiceManagerAwareInterface
{
    protected $userMapper;

    protected $userService;

    protected $serviceManager;

    public function authenticate(AuthEvent $e)
    {
        if ($this->isSatisfied()) {
            $storage = $this->getStorage()->read();
            $e->setIdentity($storage['identity'])
            ->setCode(AuthenticationResult::SUCCESS)
            ->setMessages(array('Authentication successful.'));

            return;
        }

        $service = $this->getUserService();
        $token = $e->getRequest()->getQuery()->get('token');
        $validator = new \Zend\Validator\Hex();
        if (!$validator->isValid($token)) {
            return false;
        }

        // Find the request key in the database
        $validation = $service->findByRequestKey($token);
        if (! $validation) {
            return false;
        }

        $user = $service->getUserMapper()->findByEmail($validation->getEmailAddress());

        if (!$user) {
            return false;
        }

        // Success!
        $service->remove($validation);
        $user->setState(1);
        $service->getUserMapper()->update($user);

        $e->setIdentity($user->getId());

        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)
        ->setMessages(array('Authentication successful.'))
        ->stopPropagation();
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
     * @return void
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceManager()->get('zfcuser_user_service');
        }

        return $this->userService;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;

        return $this;
    }
}
