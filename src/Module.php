<?php
/**
 * dependency Core
 * @author gbesson
 *
 */
namespace PlaygroundUser;

use PlaygroundUser\View\Strategy\RedirectionStrategy;

use Laminas\Session\Container;
use Laminas\Validator\AbstractValidator;
use LmcUser\Module as LmcUser;
use Laminas\Mvc\MvcEvent;

class Module
{

    public function onBootstrap($e)
    {
        /*
            // In some cases, this listener overrides the entity of User definition
            $doctrine = $sm->get('doctrine.entitymanager.orm_default');
            $evm = $doctrine->getEventManager();

            $listener = new  \Doctrine\ORM\Tools\ResolveTargetEntityListener();
            $listener->addResolveTargetEntity(
            'PlaygroundUser\Entity\UserInterface',
            'PlaygroundUser\Entity\User',
            array()
            );
            $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $listener);
        */

        if (PHP_SAPI !== 'cli') {
            // sponsor
            $key = $e->getRequest()->getQuery()->get('key');
            if ($key) {
                setcookie("key", $key, time() + 2000000, '/', null, null, true);
            }

            // Remember me feature
            $session     = new \Laminas\Session\Container('lmcuser');
            $cookieLogin = $session->offsetGet("cookieLogin");

            $cookie = $e->getRequest()->getCookie();
            // do autologin only if not done before and cookie is present

            if (isset($cookie['remember_me']) && $cookieLogin == false) {
                $adapter = $e->getApplication()->getServiceManager()->get('LmcUser\Authentication\Adapter\AdapterChain');
                $adapter->prepareForAuthentication($e->getRequest());
                $authService = $e->getApplication()->getServiceManager()->get('lmcuser_auth_service');

                $authService->authenticate($adapter);
            }
        }
        $sm = $e->getApplication()->getServiceManager();
        $em = $e->getApplication()->getEventManager();

        $config = $sm->get('config');

        $options    = $sm->get('playgroundcore_module_options');
        $locale     = $options->getLocale();
        $translator = $sm->get('MvcTranslator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $sm->get('ViewHelperManager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }
        AbstractValidator::setDefaultTranslator($translator, 'playgrounduser');

        // If cron is called, the $e->getRequest()->getQuery()->get('key'); produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Laminas\Console\Request')) {
            return;
        }
        // $em->attach("dispatch", function ($e) {
        //         $session = new Container('sponsorship');
        //         $key = $e->getRequest()->getQuery()->get('key');
        //     if ($key) {
        //         //$session->offsetSet('key', $key);
        //         setcookie("key", $key, time() + 200000, '/', null, null, true);
        //     }
        // });

        /**
         * This listener gives the possibility to select the layout on module / controller / action level !
         * Just configure it in any module config or autoloaded config.
         */
        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            \Laminas\Mvc\Controller\AbstractActionController::class,
            'dispatch',
            function ($e) {
                $config     = $e->getApplication()->getServiceManager()->get('config');
                if (isset($config['core_layout'])) {
                    $controller      = $e->getTarget();
                    $controllerClass = get_class($controller);
                    $moduleName      = strtolower(substr($controllerClass, 0, strpos($controllerClass, '\\')));
                    $match           = $e->getRouteMatch();
                    $routeName       = $match->getMatchedRouteName();
                    $areaName        = (strpos($routeName, '/'))?substr($routeName, 0, strpos($routeName, '/')):$routeName;
                    $areaName        = ($areaName == 'frontend' || $areaName == 'admin')? $areaName : 'frontend';
                    $actionName      = $match->getParam('action', 'not-found');

                    $user = $controller->lmcUserAuthentication()->getIdentity();
                    $logFrontendUser = (isset($config['playgrounduser']['log_frontend_user'])) ? $config['playgrounduser']['log_frontend_user'] : false;
                    $logAdminUser = (isset($config['playgrounduser']['log_admin_user'])) ? $config['playgrounduser']['log_admin_user'] : false;

                    // echo '$controllerClass : ' . $controllerClass . '<br/>';
                    // echo '$moduleName : ' .$moduleName. '<br/>';
                    // echo '$routeName : '.$routeName. '<br/>';
                    // echo '$areaName : '.$areaName. '<br/>';
                    // echo '$controllerName : ' .$controllerName. '<br/>';
                    // echo '$actionName : ' . $actionName. '<br/>';
                    if ($user) {
                        if (($logAdminUser && $areaName === 'admin') || ($logFrontendUser && $areaName === 'frontend')) {
                            $mapper = $e->getApplication()->getServiceManager()->get('playgrounduser_userlog_mapper');
                            $userLog = new \PlaygroundUser\Entity\UserLog();
                            $userLog->setUser($user)
                                ->setControllerClass($controllerClass)
                                ->setModuleName($moduleName)
                                ->setRouteName($routeName)
                                ->setAreaName($areaName)
                                ->setUri($e->getRequest()->getRequestUri())
                                ->setActionName($actionName);
                            $mapper->insert($userLog);
                        }
                    }
                }
            },
            50
        );

        // Automatically add Facebook app_id and scope for authentication
        $e->getApplication()->getEventManager()->attach(\Laminas\Mvc\MvcEvent::EVENT_RENDER, function (\Laminas\Mvc\MvcEvent $e) use ($sm) {
                $view = $sm->get('ViewHelperManager');
                $plugin = $view->get('facebookLogin');
                $plugin();
        });

        // I can post cron tasks to be scheduled by the core cron service
        $em->getSharedManager()->attach('Laminas\Mvc\Application', 'getCronjobs', array($this, 'addCronjob'));

        if (PHP_SAPI !== 'cli') {
            if (!empty($config['playgrounduser']['anonymous_tracking']) && $config['playgrounduser']['anonymous_tracking']) {
                // We set an anonymous cookie. No usage yet else but persisting it in a game entry.
                if ($e->getRequest()->getCookie() && $e->getRequest()->getCookie()->offsetExists('pg_anonymous')) {
                    $anonymousId = $e->getRequest()->getCookie()->offsetGet('pg_anonymous');
                } else {
                    $anonymousId = uniqid('pg_', true);
                }

                // Set the cookie as long as possible (limited by integer max in 32 bits
                $cookie = new \Laminas\Http\Header\SetCookie('pg_anonymous', $anonymousId, 2147483647, '/');
                $e->getResponse()->getHeaders()->addHeader($cookie);
            }

            // Redirect strategy associated to BjyAuthorize module
            $strategy = new RedirectionStrategy();
            $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($strategy, 'onDispatchError'), 200);

            $e->getApplication()->getEventManager()->getSharedManager()->attach(
                '*',
                'authenticate.post',
                [$this, 'lastLogin']
            );
        }
    }

    /**
     * This method updates the last login field of the user
     *
     * @param  MvcEvent $e
     * @return array
     */
    public function lastLogin(\Laminas\EventManager\Event $e)
    {
        $user = $e->getParam('user');
        $user->setLastLogin(new \DateTime());

        $userMapper = $e->getTarget()->getServiceManager()->get('lmcuser_user_mapper');

        $user = $userMapper->update($user);

        return $user;

    }

    /**
     * This method get the cron config for this module an add them to the listener
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
            'args'      => array('period'      => 7),
        );

        return $cronjobs;
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => [
                'userLoginWidget' => function ($sm) {
                    $viewHelper = new View\Helper\UserLoginWidget;
                    $viewHelper->setViewTemplate($sm->get('lmcuser_module_options')->getUserLoginWidgetViewTemplate());
                    $viewHelper->setLoginForm($sm->get('lmcuser_login_form'));

                    return $viewHelper;
                },
                'facebookLogin' => function ($sm) {
                    $config = $sm->get('SocialConfig');
                    $renderer = $sm->get('Laminas\View\Renderer\RendererInterface');

                    $helper = new View\Helper\FacebookLogin($config, $sm->get('Request'), $renderer);

                    return $helper;
                },
                \PlaygroundUser\View\Helper\UserListWidget::class =>  \PlaygroundUser\View\Helper\UserListWidgetFactory::class,
            ],
            'aliases' => [
                'userListWidget' => \PlaygroundUser\View\Helper\UserListWidget::class,
            ]
        );
    }

    public function getServiceConfig()
    {
        return array(
            'allow_override' => true,
            'aliases' => array(
                'translator' => 'MvcTranslator',
                'playgrounduser_message' => 'playgroundcore_message',
                'lmcuser_user_service'   => 'playgrounduser_user_service',
                'playgrounduser_user_service' => \PlaygroundUser\Service\User::class,
                'playgrounduser_rememberme_service'             => \PlaygroundUser\Service\RememberMe::class,
                'playgrounduser_team_service'                   => \PlaygroundUser\Service\Team::class,
                'playgrounduser_password_service'               => \PlaygroundUser\Service\Password::class,
                'playgrounduser_cron_service'                   => \PlaygroundUser\Service\Cron::class,
                'playgrounduser_provider_service'               => \PlaygroundUser\Service\Provider::class,
                'playgrounduser_authentication_emailvalidation' => \PlaygroundUser\Authentication\Adapter\EmailValidation::class,
                'playgrounduser_authentication_hybridauth'      => \PlaygroundUser\Authentication\Adapter\HybridAuth::class,
            ),
            'invokables'  => array(
                'PlaygroundUser\Form\Login'                  => 'PlaygroundUser\Form\Login',
                'playgrounduser_redirectionstrategy_service' => 'PlaygroundUser\View\Strategy\RedirectionStrategy',
            ),

            'factories' => array(
                \PlaygroundUser\Service\User::class => \PlaygroundUser\Service\Factory\UserFactory::class,
                \PlaygroundUser\Service\RememberMe::class => \PlaygroundUser\Service\Factory\RememberMeFactory::class,
                \PlaygroundUser\Service\Factory\Team::class => \PlaygroundUser\Service\Factory\TeamFactory::class,
                \PlaygroundUser\Service\Password::class => \PlaygroundUser\Service\Factory\PasswordFactory::class,
                \PlaygroundUser\Service\Cron::class => \PlaygroundUser\Service\Factory\CronFactory::class,
                \PlaygroundUser\Service\Provider::class => \PlaygroundUser\Service\Factory\ProviderFactory::class,
                \PlaygroundUser\Authentication\Adapter\Cookie::class  => \PlaygroundUser\Service\Factory\CookieAdapterFactory::class,
                \PlaygroundUser\Authentication\Adapter\EmailValidation::class => \PlaygroundUser\Service\Factory\EmailValidationAdapterFactory::class,
                \PlaygroundUser\Authentication\Adapter\HybridAuth::class => \PlaygroundUser\Service\Factory\HybridAuthAdapterFactory::class,
                //'LmcUser\Authentication\Adapter\AdapterChain'   => \PlaygroundUser\Service\Factory\AuthenticationAdapterChainFactory::class,
                'lmcuser_module_options'                        => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['lmcuser'])?$config['lmcuser']:array());
                },
                'lmcuser_user_mapper' => function ($sm) {
                    return new \PlaygroundUser\Mapper\User(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('lmcuser_module_options')
                    );
                },
                'lmcuser_login_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $options = $sm->get('lmcuser_module_options');
                    $form = new Form\Login(null, $options, $translator);
                    $form->setInputFilter(new \PlaygroundUser\Form\LoginFilter($options));

                    return $form;
                },

                'lmcuser_register_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $lmcuserOptions = $sm->get('lmcuser_module_options');
                    $form = new Form\Register(null, $lmcuserOptions, $translator, $sm);
                    //$form->setCaptchaElement($sm->get('lmcuser_captcha_element'));
                    $form->setInputFilter(
                        new Form\RegisterFilter(
                            new \LmcUser\Validator\NoRecordExists(
                                array(
                                    'mapper' => $sm->get('lmcuser_user_mapper'),
                                    'key'    => 'email',
                                )
                            ),
                            new \LmcUser\Validator\NoRecordExists(
                                array(
                                    'mapper' => $sm->get('lmcuser_user_mapper'),
                                    'key'    => 'username',
                                )
                            ),
                            $lmcuserOptions
                        )
                    );

                    return $form;
                },

                'PlaygroundUser\View\Strategy\UnauthorizedStrategy' => function ($sm) {
                    return new View\Strategy\UnauthorizedStrategy;
                },

                'SocialConfig' => function ($sm) {
                    $config = $sm->get('Config');
                    $config = isset($config['playgrounduser']['social'])?$config['playgrounduser']['social']:array('providers' => array());

                    $router = $sm->get('HttpRouter');
                    // Bug when using doctrine from console https://github.com/SocalNick/ScnSocialAuth/issues/67
                    if ($router instanceof \Laminas\Router\Http\TreeRouteStack) {
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
                                'name'            => 'frontend/lmcuser/backend',
                                'force_canonical' => true,
                            )
                        );
                    }

                    // If it's a console request (phpunit or doctrine console)...
                    if (PHP_SAPI === 'cli') {
                        $_SERVER['HTTP_HOST'] = '127.0.0.1'.
                        $_SERVER['REQUEST_URI'] = 'backend';
                    }

                    // this following config doesn't work with bjyprofiler
                    //https://github.com/SocalNick/ScnSocialAuth/issues/57
                    //$urlHelper = $sm->get('ViewHelperManager')->get('url');
                    //$config['base_url'] = $urlHelper('frontend/lmcuser/backend',array(), array('force_canonical' => true));
                    return $config;
                },

                'HybridAuth' => function ($sm) {
                    $config = $sm->get('SocialConfig');

                    try {
                        $auth = new \Hybridauth\Hybridauth($config);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage(), $e->getCode());
                    }
                    return $auth;
                },

                'playgrounduser_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgrounduser'])?$config['playgrounduser']:array());
                },

                'playgrounduser_change_info_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $options = $sm->get('playgrounduser_module_options');
                    $form = new Form\ChangeInfo(null, $options, $translator, $sm);

                    return $form;
                },

                'playgrounduser_user_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $lmcuserOptions = $sm->get('lmcuser_module_options');
                    $form = new Form\Register(null, $lmcuserOptions, $translator, $sm);

                    return $form;
                },

                'playgrounduseradmin_register_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $lmcuserOptions = $sm->get('lmcuser_module_options');
                    $playgroundUserOptions = $sm->get('playgrounduser_module_options');
                    $form = new Form\Admin\User(null, $playgroundUserOptions, $lmcuserOptions, $translator, $sm);
                    $filter = new Form\RegisterFilter(
                        new \LmcUser\Validator\NoRecordExists(
                            array(
                                'mapper' => $sm->get('lmcuser_user_mapper'),
                                'key'    => 'email',
                            )
                        ),
                        new \LmcUser\Validator\NoRecordExists(
                            array(
                                'mapper' => $sm->get('lmcuser_user_mapper'),
                                'key'    => 'username',
                            )
                        ),
                        $lmcuserOptions
                    );
                    if ($playgroundUserOptions->getCreateUserAutoPassword()) {
                        $filter->remove('password')->remove('passwordVerify');
                    }
                    $form->setInputFilter($filter);

                    return $form;
                },

                'playgrounduseradmin_role_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $playgroundUserOptions = $sm->get('playgrounduser_module_options');
                    $form = new Form\Admin\Role(null, $playgroundUserOptions, $translator, $sm);

                    return $form;
                },

                'playgrounduser_team_mapper' => function ($sm) {

                    return new \PlaygroundUser\Mapper\Team(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgrounduser_module_options')
                    );
                },

                'playgrounduser_rememberme_mapper' => function ($sm) {

                    return new \PlaygroundUser\Mapper\RememberMe(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgrounduser_module_options')
                    );
                },

                'playgrounduser_emailverification_mapper' => function ($sm) {
                    $mapper = new \PlaygroundUser\Mapper\EmailVerification(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('lmcuser_module_options')
                    );
                    $mapper->setEventManager($sm->get('SharedEventManager'));

                    return $mapper;
                },

                'playgrounduser_role_mapper' => function ($sm) {
                    return new Mapper\Role(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgrounduser_module_options')
                    );
                },

                'playgrounduser_forgot_form' => function ($sm) {
                    $options = $sm->get('playgrounduser_module_options');
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Forgot(null, $options, $translator);
                    $form->setInputFilter(new Form\ForgotFilter($options));

                    return $form;
                },

                'playgrounduser_reset_form' => function ($sm) {
                    $options = $sm->get('playgrounduser_module_options');
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Reset(null, $options, $translator);
                    $form->setInputFilter(new Form\ResetFilter($options, $translator));

                    return $form;
                },

                'playgrounduser_blockaccount_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $options = $sm->get('lmcuser_module_options');
                    $form = new Form\BlockAccount(null, $sm->get('lmcuser_module_options'), $translator);
                    $form->setInputFilter(new Form\BlockAccountFilter($options));

                    return $form;
                },

                'playgrounduser_newsletter_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $options = $sm->get('lmcuser_module_options');
                    $form = new Form\Newsletter(null, $sm->get('lmcuser_module_options'), $translator);
                    $form->setInputFilter(new Form\NewsletterFilter($options));

                    return $form;
                },

                'playgrounduser_address_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $options = $sm->get('playgrounduser_module_options');
                    $form = new Form\Address(null, $options, $translator, $sm);

                    return $form;
                },

                'playgrounduser_password_mapper' => function ($sm) {
                    //                     $options = $sm->get('playgrounduser_module_options');
                    //                     $mapper = new Mapper\Password;
                    //                     $mapper->setDbAdapter($sm->get('lmcuser_zend_db_adapter'));
                    //                     $entityClass = $options->getPasswordEntityClass();
                    //                     $mapper->setEntityPrototype(new $entityClass);
                    //                     $mapper->setHydrator(new Mapper\PasswordHydrator());
                    $mapper = new Mapper\Password(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );

                    return $mapper;
                },

                'playgrounduser_userprovider_mapper' => function ($sm) {
                    $mapper = new Mapper\UserProvider(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgrounduser_module_options')
                    );
                    $mapper->setEventManager($sm->get('SharedEventManager'));

                    return $mapper;
                },

                'playgrounduser_userlog_mapper' => function ($sm) {
                    $mapper = new Mapper\UserLog(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgrounduser_module_options')
                    );

                    return $mapper;
                },

                'playgrounduser_contact_form' => function ($sm) {
                    $translator = $sm->get('MvcTranslator');
                    $form = new Form\Contact(null, $sm, $translator);

                    return $form;
                },
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ .'/../config/module.config.php';
    }
}
