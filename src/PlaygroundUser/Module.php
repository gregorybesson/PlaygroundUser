<?php
/**
 * dependency Core
 * @author gbesson
 *
 */
namespace PlaygroundUser;

use Zend\Session\Container;
use Zend\Http\Request as HttpRequest;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use ZfcUser\Module as ZfcUser;
use Zend\Validator\AbstractValidator;

class Module
{
    public function init()
    {
    }

    public function onBootstrap($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $em = $e->getApplication()->getEventManager();

        //Set the translator for default validation messages
        $translator = $sm->get('translator');
        AbstractValidator::setDefaultTranslator($translator,'playgroundcore');

        $doctrine = $sm->get('doctrine.entitymanager.orm_default');
        $evm = $doctrine->getEventManager();



        /* In some cases, this listener overrides those described further in application.config.php
        $listener = new  \Doctrine\ORM\Tools\ResolveTargetEntityListener();
        $listener->addResolveTargetEntity(
        		'PlaygroundUser\Entity\UserInterface',
        		'PlaygroundUser\Entity\User',
        		array()
        );
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $listener);
        */

        //$options = $sm->get('zfcuser_module_options');
        //$reader = new AnnotationReader();

        /*
        // Add the default entity driver only if specified in configuration
        if ($options->getEnableDefaultEntities()) {
            $chain = $sm->get('doctrine.driver.orm_default');
            $chain->addDriver(new AnnotationDriver($reader, array(__DIR__.'/src/PlaygroundUser/Entity')), 'PlaygroundUser\Entity');
        }

        if (!$e->getRequest() instanceof HttpRequest) {
            return;
        }*/

        /*$session = new \Zend\Session\Container('zfcuser');
        $cookieLogin = $session->offsetGet("cookieLogin");

        $cookie = $e->getRequest()->getCookie();
        // do autologin only if not done before and cookie is present

        if (isset($cookie['remember_me']) && $cookieLogin == false) {
            $adapter = $e->getApplication()->getServiceManager()->get('ZfcUser\Authentication\Adapter\AdapterChain');
            $adapter->prepareForAuthentication($e->getRequest());
            $authService = $e->getApplication()->getServiceManager()->get('zfcuser_auth_service');

            $auth = $authService->authenticate($adapter);
        }*/

        // If cron is called, the $e->getRequest()->getQuery()->get('key'); produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Zend\Console\Request')) {
            return;
        }
        $em->attach("dispatch", function($e) {
            $session = new Container('sponsorship');
            $key = $e->getRequest()->getQuery()->get('key');
            if ($key) {
                $session->offsetSet('key',  $key);
            }
        });

        // I can post cron tasks to be scheduled by the core cron service
        $em->getSharedManager()->attach('Zend\Mvc\Application','getCronjobs', array($this, 'addCronjob'));
    }

    /**
     * This method get the cron config for this module an add them to the listener
     * TODO : déporter la def des cron dans la config.
     *
     * @param  EventManager $e
     * @return array
     */
    public function addCronjob($e)
    {
        $cronjobs = $e->getParam('cronjobs');

        // This cron job is scheduled everyday @ 2AM en disable user in state 0 since 'period' (7 days here)
        $cronjobs['playgrounduser_disable'] = array(
            'frequency' => '0 2 * * *',
            'callback'  => '\PlaygroundUser\Service\Cron::disableUser',
            'args'      => array('period' => 7),
        );

        return $cronjobs;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'userLoginWidget' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserLoginWidget;
                    $viewHelper->setViewTemplate($locator->get('zfcuser_module_options')->getUserLoginWidgetViewTemplate());
                    $viewHelper->setLoginForm($locator->get('zfcuser_login_form'));

                    return $viewHelper;
                },
            ),
        );

    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                    'zfcuser_doctrine_em'  => 'doctrine.entitymanager.orm_default',
                    'playgrounduser_message'    => 'playgroundcore_message',
            ),

            'invokables' => array(
                    'PlaygroundUser\Authentication\Adapter\Cookie' => 'PlaygroundUser\Authentication\Adapter\Cookie',
                    'PlaygroundUser\Form\Login'                    => 'PlaygroundUser\Form\Login',
                    'playgrounduser_user_service'                  => 'PlaygroundUser\Service\User',
                    'playgrounduser_rememberme_service'            => 'PlaygroundUser\Service\RememberMe',
                    'playgrounduser_password_service'              => 'PlaygroundUser\Service\Password',
                    'zfcuser_user_service'                    => 'PlaygroundUser\Service\User', // Extending ZfcUser service
                    'playgrounduser_cron_service'                  => 'PlaygroundUser\Service\Cron',
                    'playgrounduser_provider_service'              => 'PlaygroundUser\Service\Provider',
               ),

            'factories' => array(
                'playgrounduser_authentication_emailvalidation'    => 'PlaygroundUser\Service\Factory\EmailValidationAdapterFactory',
                'playgrounduser_authentication_hybridauth'         => 'PlaygroundUser\Service\Factory\HybridAuthAdapterFactory',
                'ZfcUser\Authentication\Adapter\AdapterChain' => 'PlaygroundUser\Service\Factory\AuthenticationAdapterChainFactory',
                'zfcuser_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['zfcuser']) ? $config['zfcuser'] : array());
                },
                'zfcuser_user_mapper' => function ($sm) {
                    return new \PlaygroundUser\Mapper\User(
                        $sm->get('zfcuser_doctrine_em'),
                        $sm->get('zfcuser_module_options')
                    );
                },
                'zfcuser_login_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $options = $sm->get('zfcuser_module_options');
                    $form = new Form\Login(null, $options, $translator);
                    $form->setInputFilter(new \ZfcUser\Form\LoginFilter($options));

                    return $form;
                },

                'zfcuser_register_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $zfcUserOptions = $sm->get('zfcuser_module_options');
                    $form = new Form\Register(null, $zfcUserOptions, $translator, $sm );
                    //$form->setCaptchaElement($sm->get('zfcuser_captcha_element'));
                    $form->setInputFilter(new \ZfcUser\Form\RegisterFilter(
                        new \ZfcUser\Validator\NoRecordExists(array(
                            'mapper' => $sm->get('zfcuser_user_mapper'),
                            'key'    => 'email'
                        )),
                        new \ZfcUser\Validator\NoRecordExists(array(
                            'mapper' => $sm->get('zfcuser_user_mapper'),
                            'key'    => 'username'
                        )),
                        $zfcUserOptions
                    ));

                    return $form;
                },

                'PlaygroundUser\View\Strategy\UnauthorizedStrategy' => function ($sm) {
                	return new View\Strategy\UnauthorizedStrategy;
                },

                'SocialConfig' => function($sm) {
                $config = $sm->get('Config');
                $config = isset($config['playgrounduser']['social']) ? $config['playgrounduser']['social'] : array('providers'=>array());

                $router = $sm->get('Router');
                // Bug when using doctrine from console https://github.com/SocalNick/ScnSocialAuth/issues/67
                if ($router instanceof \Zend\Mvc\Router\Http\TreeRouteStack) {
                    $request = $sm->get('Request');
                    if (!$router->getRequestUri() && method_exists($request, 'getUri')) {
                        $router->setRequestUri($request->getUri());
                    }
                    if (!$router->getBaseUrl() && method_exists($request, 'getBaseUrl')) {
                        $router->setBaseUrl($request->getBaseUrl());
                    }
                    $config['base_url'] = $router->assemble(
                        array(),
                        array(
                            'name' => 'frontend/zfcuser/backend',
                            'force_canonical' => true,
                        )
                    );
                }

                // If it's a console request (phpunit or doctrine console)...
                if (PHP_SAPI === 'cli') {
                    $_SERVER['HTTP_HOST'] = '127.0.0.1'.
                    $_SERVER['REQUEST_URI'] = 'frontend/zfcuser/backend';
                }

                // this following config doesn't work with bjyprofiler
                //https://github.com/SocalNick/ScnSocialAuth/issues/57
                //$urlHelper = $sm->get('viewhelpermanager')->get('url');
                //$config['base_url'] = $urlHelper('frontend/zfcuser/backend',array(), array('force_canonical' => true));
                return $config;
                },

                'HybridAuth' => function($sm) {
                    $config = $sm->get('SocialConfig');

                   try{
                    	$auth = new \Hybrid_Auth($config);
                    }catch(\Exception $e){
                    	throw new \Exception($e->getMessage(), $e->getCode());
                    }
                    return $auth;
                },

                'playgrounduser_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgrounduser']) ? $config['playgrounduser'] : array());
                },

                'playgrounduser_user_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $zfcUserOptions = $sm->get('zfcuser_module_options');
                    $form = new Form\Register(null, $zfcUserOptions, $translator);

                    return $form;
                },

                'playgrounduseradmin_register_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $zfcUserOptions = $sm->get('zfcuser_module_options');
                    $playgroundUserOptions = $sm->get('playgrounduser_module_options');
                    $form = new Form\Admin\User(null, $playgroundUserOptions, $zfcUserOptions, $translator, $sm );
                    $filter = new \ZfcUser\Form\RegisterFilter(
                        new \ZfcUser\Validator\NoRecordExists(array(
                            'mapper' => $sm->get('zfcuser_user_mapper'),
                            'key'    => 'email'
                        )),
                        new \ZfcUser\Validator\NoRecordExists(array(
                            'mapper' => $sm->get('zfcuser_user_mapper'),
                            'key'    => 'username'
                        )),
                        $zfcUserOptions
                    );
                    if ($playgroundUserOptions->getCreateUserAutoPassword()) {
                        $filter->remove('password')->remove('passwordVerify');
                    }
                    $form->setInputFilter($filter);

                    return $form;
                },

                'playgrounduser_rememberme_mapper' => function ($sm) {
                    $options = $sm->get('zfcuser_module_options');
                    $rememberOptions = $sm->get('playgrounduser_module_options');
                    $mapper = new Mapper\RememberMe;
                    $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
                    $entityClass = $rememberOptions->getRememberMeEntityClass();
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\RememberMeHydrator());

                    return $mapper;
                },

                'playgrounduser_emailverification_mapper' => function ($sm) {
                    return new \PlaygroundUser\Mapper\EmailVerification(
                        $sm->get('zfcuser_doctrine_em'),
                        $sm->get('zfcuser_module_options')
                    );
                },

                'playgrounduser_role_mapper' => function ($sm) {
                    return new Mapper\Role(
                        $sm->get('zfcuser_doctrine_em'),
                        $sm->get('playgrounduser_module_options')
                    );
                },

                'playgrounduser_forgot_form' => function($sm) {
                    $options = $sm->get('playgrounduser_module_options');
                    $translator = $sm->get('translator');
                    $form = new Form\Forgot(null, $options, $translator);
                    $form->setInputFilter(new Form\ForgotFilter($options));

                    return $form;
                },

                'playgrounduser_reset_form' => function($sm) {
                    $options = $sm->get('playgrounduser_module_options');
                    $translator = $sm->get('translator');
                    $form = new Form\Reset(null, $options, $translator);
                    $form->setInputFilter(new Form\ResetFilter($options, $translator));

                    return $form;
                },

                'playgrounduser_change_info_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $options = $sm->get('playgrounduser_module_options');
                    $form = new Form\ChangeInfo(null, $options, $translator);

                    return $form;
                },

                'playgrounduser_blockaccount_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $options = $sm->get('zfcuser_module_options');
                    $form = new Form\BlockAccount(null, $sm->get('zfcuser_module_options'), $translator);
                    $form->setInputFilter(new Form\BlockAccountFilter($options));

                    return $form;
                },

                'playgrounduser_newsletter_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $options = $sm->get('zfcuser_module_options');
                    $form = new Form\Newsletter(null, $sm->get('zfcuser_module_options'), $translator);
                    $form->setInputFilter(new Form\NewsletterFilter($options));

                    return $form;
                },

                'playgrounduser_address_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $options = $sm->get('playgrounduser_module_options');
                    $form = new Form\Address(null, $options, $translator);

                    return $form;
                },

                'playgrounduser_password_mapper' => function ($sm) {
                    $options = $sm->get('playgrounduser_module_options');
                    $mapper = new Mapper\Password;
                    $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
                    $entityClass = $options->getPasswordEntityClass();
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\PasswordHydrator());

                    return $mapper;
                },

                'playgrounduser_userprovider_mapper' => function ($sm) {
                    return new Mapper\UserProvider(
                        $sm->get('zfcuser_doctrine_em'),
                        $sm->get('playgrounduser_module_options')
                    );
                },
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
