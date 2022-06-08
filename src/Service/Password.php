<?php

namespace PlaygroundUser\Service;

use LmcUser\Options\PasswordOptionsInterface;
use PlaygroundUser\Options\ForgotOptionsInterface;
use Laminas\ServiceManager\ServiceManager;
use PlaygroundUser\Mapper\UserInterface as UserMapperInterface;
use PlaygroundUser\Mapper\Password as PasswordMapper;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\EventManager\EventManager;

class Password
{
    use EventManagerAwareTrait;

    /**
     * @var ModelMapper
     */
    protected $passwordMapper;
    protected $userMapper;
    protected $serviceManager;
    protected $options;
    protected $lmcuserOptions;
    protected $event;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceManager = $locator;
    }

    public function findByRequestKey($token)
    {
        return $this->getPasswordMapper()->findByRequestKey($token);
    }

    public function findByEmail($email)
    {
        return $this->getPasswordMapper()->findByEmail($email);
    }

    public function cleanExpiredForgotRequests()
    {
        return $this->getPasswordMapper()->cleanExpiredForgotRequests();
    }

    public function cleanPriorForgotRequests($userId)
    {
        return $this->getPasswordMapper()->cleanPriorForgotRequests($userId);
    }

    public function remove($m)
    {
        return $this->getPasswordMapper()->remove($m);
    }

    public function sendProcessForgotRequest($user, $email)
    {
        //Invalidate all prior request for a new password
        $userId = $user->getId();
        $this->cleanPriorForgotRequests($userId);

        $class = $this->getOptions()->getPasswordEntityClass();
        $model = new $class;
        $model->setUserId($userId)->setRequestTime(new \DateTime('now'));
        $model->generateRequestKey();
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('record' => $model, 'userId' => $userId));
        $this->getPasswordMapper()->persist($model);

        $this->sendForgotEmailMessage($user, $model);
    }

    public function sendForgotEmailMessage($user, $model)
    {
        $mailService = $this->getServiceManager()->get('playgrounduser_message');

        $from = $this->getOptions()->getEmailFromAddress();
        $to = $user->getEmail();
        $subject = $this->getServiceManager()->get('MvcTranslator')->translate($this->getOptions()->getResetEmailSubjectLine(), 'playgrounduser');

        $renderer = $this->getServiceManager()->get('Laminas\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url('frontend', array(), array('force_canonical' => true));

        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-user/email/forgot', array('record' => $model, 'to' => $to, 'skinUrl' => $skinUrl, 'user' => $user));

        $mailService->send($message);
    }

    public function resetPassword($password, $user, array $data)
    {
        $newPass = $data['newCredential'];

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($this->getLmcUserOptions()->getPasswordCost());

        $pass = $bcrypt->create($newPass);
        $user->setPassword($pass);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
        $this->getUserMapper()->update($user);
        $this->remove($password);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user));

        return true;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('lmcuser_user_mapper');
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

    public function setPasswordMapper(PasswordMapper $passwordMapper)
    {
        $this->passwordMapper = $passwordMapper;

        return $this;
    }

    public function getPasswordMapper()
    {
        if (null === $this->passwordMapper) {
            $this->setPasswordMapper($this->getServiceManager()->get('playgrounduser_password_mapper'));
        }

        return $this->passwordMapper;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ForgotOptionsInterface) {
            $this->setOptions($this->getServiceManager()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }

    public function setOptions(ForgotOptionsInterface $opt)
    {
        $this->options = $opt;

        return $this;
    }

    public function getLmcUserOptions()
    {
        if (!$this->lmcuserOptions instanceof PasswordOptionsInterface) {
            $this->setLmcUserOptions($this->getServiceManager()->get('lmcuser_module_options'));
        }

        return $this->lmcuserOptions;
    }

    public function setLmcUserOptions(PasswordOptionsInterface $lmcuserOptions)
    {
        $this->lmcuserOptions = $lmcuserOptions;

        return $this;
    }

    public function getEventManager()
    {
        if ($this->event === NULL) {
            $this->event = new EventManager(
                $this->getServiceManager()->get('SharedEventManager'), [get_class($this)]
            );
        }
        return $this->event;
    }
}
