<?php

namespace PlaygroundUser\Authentication\Adapter;

use LmcUser\Authentication\Adapter\AbstractAdapter;
use LmcUser\Authentication\Adapter\AdapterChainEvent;
use Laminas\Authentication\Result as AuthenticationResult;
use Laminas\EventManager\EventInterface as AuthEvent;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\ServiceLocatorInterface;

class EmailValidation extends AbstractAdapter
{
    protected $userMapper;

    protected $userService;

    protected $serviceManager;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceManager = $locator;
    }

    public function authenticate(AdapterChainEvent $e)
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
        $validator = new \Laminas\Validator\Hex();
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
        ->setMessages(array('Authentication successful.'));
        //->stopPropagation();
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
            $this->userService = $this->getServiceManager()->get('lmcuser_user_service');
        }

        return $this->userService;
    }

    public function setUserService(\PlaygroundUser\Service\User $userService)
    {
        $this->userService = $userService;

        return $this;
    }
}
