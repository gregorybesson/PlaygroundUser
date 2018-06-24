<?php

namespace PlaygroundUser\Service;

use ZfcUser\Options\PasswordOptionsInterface;
use PlaygroundUser\Options\ForgotOptionsInterface;
use Zend\ServiceManager\ServiceManager;
use PlaygroundUser\Mapper\UserInterface as UserMapperInterface;
use PlaygroundUser\Mapper\Password as PasswordMapper;
use Zend\Crypt\Password\Bcrypt;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

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
    protected $zfcUserOptions;
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

    public function sendProcessForgotRequest($userId, $email)
    {
        //Invalidate all prior request for a new password
        $this->cleanPriorForgotRequests($userId);

        $class = $this->getOptions()->getPasswordEntityClass();
        $model = new $class;
        $model->setUserId($userId)->setRequestTime(new \DateTime('now'));
        $model->generateRequestKey();
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('record' => $model, 'userId' => $userId));
        $this->getPasswordMapper()->persist($model);

        $this->sendForgotEmailMessage($email, $model);
    }

    public function sendForgotEmailMessage($to, $model)
    {
        $mailService = $this->getServiceManager()->get('playgrounduser_message');

        $from = $this->getOptions()->getEmailFromAddress();
        $subject = $this->getServiceManager()->get('MvcTranslator')->translate($this->getOptions()->getResetEmailSubjectLine(), 'playgrounduser');

        $renderer = $this->getServiceManager()->get('Zend\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url('frontend', array(), array('force_canonical' => true));

        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-user/email/forgot', array('record' => $model, 'to' => $to, 'skinUrl' => $skinUrl));

        $mailService->send($message);
    }

    public function resetPassword($password, $user, array $data)
    {
        $newPass = $data['newCredential'];

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());

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

    public function getZfcUserOptions()
    {
        if (!$this->zfcUserOptions instanceof PasswordOptionsInterface) {
            $this->setZfcUserOptions($this->getServiceManager()->get('zfcuser_module_options'));
        }

        return $this->zfcUserOptions;
    }

    public function setZfcUserOptions(PasswordOptionsInterface $zfcUserOptions)
    {
        $this->zfcUserOptions = $zfcUserOptions;

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