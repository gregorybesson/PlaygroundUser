<?php

namespace PlaygroundUser\Options;

use ZfcUser\Options\ModuleOptions as BaseModuleOptions;

class ModuleOptions extends BaseModuleOptions implements
    UserListOptionsInterface,
    UserEditOptionsInterface,
    UserCreateOptionsInterface,
    RememberMeOptionsInterface,
    ForgotOptionsInterface
{

    protected $providers = array(
        'facebook',
    );

    /**
     * @var string
     */
    protected $social = array();

    /**
     * @var bool
     * true = create user automaticaly after social authentication
     * false = data are extracted from the social id and sent to a registration form
     */
    protected $createUserAutoSocial = false;

    /**
     * @var string
     */
    protected $rememberMeEntityClass = 'PlaygroundUser\Entity\RememberMe';

    /**
     * @var string
     */
    protected $userEntityClass = 'PlaygroundUser\Entity\User';

    /**
     * @var bool
     */
    protected $enableDefaultEntities = true;

    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * drive path to user avatar file
     */
    protected $avatar_path = 'public/media/user';

    /**
     * url path to user avatar file
     */
    protected $avatar_url = 'media/user';

    /**
     * 30 days default
     * @var int
     */
    protected $cookieExpire = 2592000;

    /**
     * @TODO: change "things" below
     * Array of "things" to show in the user list
     */
    protected $userListElements = array('Id' => 'id', 'Email address' => 'email');

    /**
     * Array of form elements to show when editing a user
     * Key = form label
     * Value = entity property(expecting a 'getProperty()/setProperty()' function)
     */
    protected $editFormElements = array('Email' => 'email', 'Password' => 'password');

    /**
     * Array of form elements to show when creating a user
     * Key = form label
     * Value = entity property(expecting a 'getProperty()/setProperty()' function)
     */
    protected $createFormElements = array('Email' => 'email', 'Password' => 'password');

    /**
     * @var bool
     * true = create password automaticly
     * false = administrator chooses password
     */
    protected $createUserAutoPassword = true;

    protected $userMapper = 'PlaygroundUser\Mapper\User';

    /**
     * @var string
     */
    protected $emailFromAddress = '';

    /**
     * @var string
     */
    protected $resetEmailSubjectLine = 'You requested to reset your password';

    /**
     * @var string
     */
    protected $newEmailSubjectLine = 'Your new password';

    /**
     * @var string
     */
    protected $verificationEmailSubjectLine = 'Activate your account';

    /**
     * @var string
     */
    protected $emailTransport = 'Zend\Mail\Transport\Sendmail';

    /**
     * @var string
     */
    protected $passwordEntityClass = 'PlaygroundUser\Entity\Password';

    /**
     * @var int
     */
    protected $resetExpire = 86400;

    /**
     * @var boolean
     */
    protected $emailVerification = false;

    /**
     * @var string
     */
    protected $defaultRegisterRole = 'user';

    /**
     * @var array
     */
    protected $admin = array(
        'route_login' => 'admin',
        'resource' => 'core',
        'privilege'   => 'dashboard',
        'controller' => 'adminstats',
        'action' => 'index',
        'route_login_fail' => 'admin'
    );

    /**
     * @return the $emailVerification
     */
    public function getEmailVerification()
    {
        return $this->emailVerification;
    }

    /**
     * @param string $emailVerification
     */
    public function setEmailVerification($emailVerification)
    {
        $this->emailVerification = $emailVerification;
    }

    /**
     * @return the $defaultRole
     */
    public function getDefaultRegisterRole()
    {
        return $this->defaultRegisterRole;
    }

    /**
     * @param string $defaultRegisterRole
     */
    public function setDefaultRegisterRole($defaultRegisterRole)
    {
        $this->defaultRegisterRole = $defaultRegisterRole;
    }

    public function getEmailFromAddress()
    {
        return $this->emailFromAddress;
    }

    public function getResetEmailSubjectLine()
    {
        return $this->resetEmailSubjectLine;
    }

    public function getEmailTransport()
    {
        return $this->emailTransport;
    }

    public function setEmailFromAddress($emailFromAddress)
    {
        $this->emailFromAddress = $emailFromAddress;

        return $this;
    }

    public function setResetEmailSubjectLine($resetEmailSubjectLine)
    {
        $this->resetEmailSubjectLine = $resetEmailSubjectLine;

        return $this;
    }

    public function setEmailTransport($emailTransport)
    {
        $this->emailTransport = $emailTransport;

        return $this;
    }

    /**
     * set user entity class name
     *
     * @param  string        $userEntityClass
     * @return ModuleOptions
     */
    public function setPasswordEntityClass($passwordEntityClass)
    {
        $this->passwordEntityClass = $passwordEntityClass;

        return $this;
    }

    /**
     * get user entity class name
     *
     * @return string
     */
    public function getPasswordEntityClass()
    {
        return $this->passwordEntityClass;
    }

    public function setResetExpire($resetExpire)
    {
        $this->resetExpire = $resetExpire;

        return $this;
    }

    public function getResetExpire()
    {
        return $this->resetExpire;
    }

    public function setUserMapper($userMapper)
    {
        $this->userMapper = $userMapper;
    }

    public function getUserMapper()
    {
        return $this->userMapper;
    }

    public function setUserListElements(array $listElements)
    {
        $this->userListElements = $listElements;
    }

    public function getUserListElements()
    {
        return $this->userListElements;
    }

    public function getEditFormElements()
    {
        return $this->editFormElements;
    }

    public function setEditFormElements(array $elements)
    {
        $this->editFormElements = $elements;
    }

    public function setCreateFormElements(array $createFormElements)
    {
        $this->createFormElements = $createFormElements;
    }

    public function getCreateFormElements()
    {
        return $this->createFormElements;
    }

    public function setCreateUserAutoPassword($createUserAutoPassword)
    {
        $this->createUserAutoPassword = $createUserAutoPassword;
    }

    public function getCreateUserAutoPassword()
    {
        return $this->createUserAutoPassword;
    }

    public function setCreateUserAutoSocial($createUserAutoSocial)
    {
        $this->createUserAutoSocial = $createUserAutoSocial;
    }

    public function getCreateUserAutoSocial()
    {
        return $this->createUserAutoSocial;
    }

    public function setCookieExpire($seconds)
    {
        $this->cookieExpire = $seconds;

        return $this;
    }

    public function getCookieExpire()
    {
        return $this->cookieExpire;
    }

    public function setRememberMeEntityClass($rememberMeEntityClass)
    {
        $this->rememberMeEntityClass = $rememberMeEntityClass;

        return $this;
    }

    public function getRememberMeEntityClass()
    {
        return $this->rememberMeEntityClass;
    }

    public function getNewEmailSubjectLine()
    {
        return $this->newEmailSubjectLine;
    }

    public function setNewEmailSubjectLine($newEmailSubjectLine)
    {
        $this->NewEmailSubjectLine = $newEmailSubjectLine;

        return $this;
    }

    public function getVerificationEmailSubjectLine()
    {
        return $this->verificationEmailSubjectLine;
    }

    public function setVerificationEmailSubjectLine($verificationEmailSubjectLine)
    {
        $this->verificationEmailSubjectLine = $verificationEmailSubjectLine;

        return $this;
    }

    /**
     * @param boolean $enableDefaultEntities
     */
    public function setEnableDefaultEntities($enableDefaultEntities)
    {
        $this->enableDefaultEntities = $enableDefaultEntities;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableDefaultEntities()
    {
        return $this->enableDefaultEntities;
    }

    /**
     * Set media path
     *
     * @param  string                           $avatar_path
     * @return \PlaygroundUser\Options\ModuleOptions
     */
    public function setAvatarPath($avatar_path)
    {
        $this->avatar_path = $avatar_path;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarPath()
    {
        return $this->avatar_path;
    }

    /**
     *
     * @param  string                           $media_url
     * @return \PlaygroundUser\Options\ModuleOptions
     */
    public function setAvatarUrl($avatar_url)
    {
        $this->avatar_url = $avatar_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatar_url;
    }

    /**
     * @return string
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * @return string
     */
    public function setSocial($social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     *
     * @param  array $admin
     * @return \PlaygroundUser\Options\ModuleOptions
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * get an array of enabled providers
     *
     * @return array
     */
    public function getEnabledProviders()
    {
        $social = $this->getSocial();
        $enabled = array();
        if (isset($social['providers'])) {
            $providers = $social['providers'];
            if (!empty($providers) && is_array($providers)) {
                foreach ($providers as $provider => $config) {
                    if ($config['enabled']) {
                        $enabled[] = strtolower($provider);
                    }
                }
            }
        }

        return $enabled;
    }
}
