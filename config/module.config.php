<?php
return array(

    'doctrine' => array(
        'driver' => array(
            'zfcuser_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundUser/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundUser\Entity'  => 'zfcuser_entity'
                )
            )
        )
    ),

    'assetic_configuration' => array(
        'modules' => array(
            'lib' => array(
                'collections' => array(
                    'fbregister_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/fbregister.js',
                        ),
                        'options' => array(
                            'move_raw' => true,
                            'output' => 'lib/js',
                        )
                    ),
                ),
            ),
        ),
    ),
    
    'bjyauthorize' => array(
        'default_role' => 'guest',
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
        'role_providers' => array(
            'BjyAuthorize\Provider\Role\Config' => array(
                'guest' => array(),
                'user'  => array('children' => array(
                    'admin' =>  array(),
                ))
            ), 
    
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'PlaygroundUser\Entity\Role',
            ),
        ),
        
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'user'          => array(),
            ),
        ),
        
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    array(array('admin'), 'user',           array('list','add','edit','delete')),
                ),
            ),
        ),
        
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
            	array('controller' => 'zfcuser',   'roles' => array('guest', 'user')),
                array('controller' => \PlaygroundUser\Controller\Frontend\UserController::class,   'roles' => array('guest', 'user')),
                array('controller' => \PlaygroundUser\Controller\Frontend\TeamController::class,   'roles' => array('guest', 'user')),
                array('controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class, 'roles' => array('guest', 'user')),
                array('controller' => \PlaygroundUser\Controller\Frontend\ContactController::class, 'roles' => array('guest', 'user')),
                
                // Admin area
                array('controller' => \PlaygroundUser\Controller\Admin\LoginController::class, 'roles' => array('guest', 'user')),
                array('controller' => \PlaygroundUser\Controller\Admin\AdminController::class, 'roles' => array('admin')),
            ),
        ),
    ),

	'data-fixture' => array(
		'PlaygroundUser_fixture' => __DIR__ . '/../src/PlaygroundUser/DataFixtures/ORM',
	),

    'core_layout' => array(
        'frontend' => array(
            'modules' => array(
                'playgrounduser' => array(
                    'layout' => 'layout/2columns-left.phtml',
                    'controllers' => array(
                        \PlaygroundUser\Controller\Frontend\UserController::class => array(
                            'children_views' => array(
                                'col_left' => 'playground-user/user/col-user.phtml'
                            ),
                            'actions' => array(
                                'index' => array(
                                    'layout' => 'layout/1column.phtml'
                                ),
                                'register' => array(
                                    'layout' => 'layout/1column.phtml'
                                ),
                                'registermail' => array(
                                    'layout' => 'layout/1column.phtml'
                                )
                            )
                        ),
                        \PlaygroundUser\Controller\Frontend\ForgotController::class => array(
                            'layout' => 'layout/1column.phtml'
                        )
                    )
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view/admin',
            __DIR__ . '/../view/frontend',
        ),
    ),

    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
        	array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../../../../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgrounduser'
            ),
            array(
                'type'         => 'phpArray',
                'base_dir'     => __DIR__ . '/../language',
                'pattern'      => '%s.php',
                'text_domain'  => 'playgrounduser'
            ),
        ),
    ),

    'controllers' => array(
        'factories' => array(
        	\PlaygroundUser\Controller\Admin\LoginController::class => \PlaygroundUser\Service\Factory\AdminUserControllerFactory::class,
            \PlaygroundUser\Controller\Admin\AdminController::class => \PlaygroundUser\Service\Factory\AdminControllerFactory::class,
            \PlaygroundUser\Controller\Frontend\UserController::class => \PlaygroundUser\Service\Factory\UserControllerFactory::class,
            \PlaygroundUser\Controller\Frontend\TeamController::class => \PlaygroundUser\Service\Factory\FrontendTeamControllerFactory::class,
            \PlaygroundUser\Controller\Frontend\ForgotController::class => \PlaygroundUser\Service\Factory\FrontendForgotControllerFactory::class,
            \PlaygroundUser\Controller\Frontend\ContactController::class => \PlaygroundUser\Service\Factory\FrontendContactControllerFactory::class,
        ),
        'aliases' => [
            'playgrounduser_user'  => \PlaygroundUser\Controller\Frontend\UserController::class,
        ]
    ),

    'router' => array(
        'routes' => array(
        	'frontend' => array(
       			'child_routes' => array(
       			    'contact' => array(
       			        'type' => 'Literal',
       			        'options' => array(
       			            'route' => 'contactez-nous',
       			            'defaults' => array(
       			                'controller' => \PlaygroundUser\Controller\Frontend\ContactController::class,
       			                'action'     => 'index',
       			            ),
       			        ),
       			        'may_terminate' => true,
       			        'child_routes' => array(
       			            'confirmation' => array(
       			                'type'    => 'Literal',
       			                'options' => array(
       			                    'route'    => '/confirmation',
       			                    'defaults' => array(
       			                        'controller' => \PlaygroundUser\Controller\Frontend\ContactController::class,
       			                        'action'     => 'confirmation',
       			                    ),
       			                ),
       			            ),
       			        ),
       			    ),
       			    'autocomplete' => array(
       			    	'type' => 'Segment',
		                'options' => array(
		                    'route' => 'autocomplete',
		                ),
		                'child_routes' => array(
		                	'user' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/user/:field/:value',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'autoCompleteUser',
		                            )
		                        )
		                    )
		                )
       			    ),
		            'zfcuser' => array(
		                'type' => 'Literal',
		                'priority' => 1000,
		                'options' => array(
		                    'route' => 'mon-compte',
		                    'defaults' => array(
		                        'controller' => 'zfcuser',
		                        'action'     => 'index',
		                    ),
		                ),
		                'may_terminate' => true,
		                'child_routes' => array(
		                	'team' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/team',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\TeamController::class,
		                                'action'     => 'index',
		                            ),
		                        ),
		                    ),
		                    'forgotpassword' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/mot-passe-oublie',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
		                                'action'     => 'forgot',
		                            ),
		                        ),
		                    ),
		                    'ajaxforgotpassword' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajax-mot-passe-oublie',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
		                                'action'     => 'ajaxforgot',
		                            ),
		                        ),
		                    ),
		                    'sentpassword' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/envoi-mot-passe[/:email]',
		                            'constraints' => array(
                                        ':email' => '[a-zA-Z0-9_-@.]+',
                                    ),
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
		                                'action'     => 'sent',
		                            ),
		                        ),
		                    ),
		                    'ajaxrenewpassword' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/ajax-renew-password',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
		                                'action'     => 'ajaxrenewPassword',
		                            ),
		                        ),
		                    ),
		                    'ajaxlogin' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajaxlogin',
		                            'defaults' => array(
		                                    'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                    'action'     => 'ajaxlogin',
		                            ),
		                        ),
		                    ),
		                    'login' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/login',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'login',
		                            ),
		                        ),
		                        'may_terminate' => true,
		                        'child_routes' => array(
		                            'provider' => array(
		                                'type' => 'Segment',
		                                'options' => array(
		                                    'route' => '/:provider',
		                                    'constraints' => array(
		                                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]+',
		                                    ),
		                                    'defaults' => array(
		                                        'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                        'action' => 'providerLogin',
		                                    ),
		                                ),
		                            ),
		                        ),
		                    ),
		                    'logout' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/logout',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'logout',
		                            ),
		                        ),
		                    ),
		                    'ajaxauthenticate' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajaxauthenticate',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'ajaxauthenticate',
		                            ),
		                        ),
		                    ),
		                    'authenticate' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                                'route' => '/authenticate',
		                                'defaults' => array(
		                                        'controller' => 'zfcuser',
		                                        'action'     => 'authenticate',
		                                ),
		                        ),
		                    ),
		                    'resetpassword' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/reset-password/:userId/:token',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
		                                'action'     => 'reset',
		                            ),
		                            'constraints' => array(
		                                'userId'  => '[0-9]+',
		                                'token' => '[A-F0-9]+',
		                            ),
		                        ),
		                    ),
		                    'changedpassword' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/changed-password/:userId',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
		                                'action'     => 'changedPassword',
		                            ),
		                            'constraints' => array(
		                                'userId'  => '[0-9]+',
		                            ),
		                        ),
		                    ),
		                    'register' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/inscription[/:socialnetwork]',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'register',
		                            ),
		                        ),
		                    ),
		                    'registermail' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/registermail',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'registermail',
		                            ),
		                        ),
		                    ),
		                    'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
		                    'backend' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/backend',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action' => 'HybridAuthBackend'
		                            )
		                        ),
		                    ),

		                    'profile' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/mes-coordonnees',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'profile',
		                            ),
		                        ),
		                    ),
		                    'profile_prizes' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/prizes',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'prizeCategoryUser',
		                            ),
		                        ),
		                    ),
		                    'newsletter' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/newsletter',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'newsletter',
		                            ),
		                        ),
		                    ),
		                    'ajax_newsletter' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajax-newsletter',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'ajaxNewsletter',
		                            ),
		                        ),
		                    ),
		                    'changepassword' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/change-password',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'changepassword',
		                            ),
		                        ),
		                    ),
		                    'blockaccount' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/block-account',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action'     => 'blockAccount',
		                            ),
		                        ),
		                    ),
		                    'changeemail' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/change-email',
		                            'defaults' => array(
		                                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
		                                'action' => 'changeemail',
		                            ),
		                        ),
		                    ),
		                ),
		            ),
       			),
        	),
            'admin' => array(
            	'options' => array(
            		'defaults' => array(
           				'controller' => \PlaygroundUser\Controller\Admin\LoginController::class,
           				'action'     => 'login',
           				'title' => 'Back-office'
            		),
            	),
                'child_routes' => array(
                	'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/logout',
                            'defaults' => array(
                                'controller' => \PlaygroundUser\Controller\Admin\LoginController::class,
                                'action'     => 'logout',
                            ),
                        ),
                    ),
                    'playgrounduser' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/user',
                            'defaults' => array(
                                'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                'action'     => 'index',
                            ),
                        ),
                        'child_routes' =>array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list/:roleId[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'list',
                                        'roleId' 	 => 'all',
                                        'filter' 	 => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                ),
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:roleId[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'download',
                                        'roleId' 	 => 'all',
                                        'filter' 	 => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/create/:userId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'create',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:userId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'edit',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:userId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'remove',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'activate' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/activate/:userId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'activate',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'reset' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset/:userId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'reset',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'listrole' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/listrole[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'listRole',
                                        'filter' 	 => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                ),
                            ),
                            'createrole' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/createrole/:roleId',
                                    'defaults' => array(
                                        'controller' => \PlaygroundUser\Controller\Admin\AdminController::class,
                                        'action'     => 'createRole',
                                        'roleId'     => 0
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'navigation' => array(
        'default' => array(
            'register' => array(
                'label' => 'Inscrivez-vous ou accédez à votre compte',
                'route' => 'inscription[/:socialnetwork]',
                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
                'action'     => 'register',
            ),
            'profile' => array(
                'label' => 'Modifier mes informations',
                'route' => 'profile',
                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
                'action'     => 'profile',
            ),
            'registermail' => array(
                'label' => 'Inscrivez-vous ou accédez à votre compte',
                'route' => 'registermail',
                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
                'action'     => 'registermail',
            ),

            'newsletter' => array(
                'label' => 'Newsletter',
                'route' => 'frontend/zfcuser/newsletter',
                'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
                'action'     => 'newsletter',
            ),
            'resetpassword' => array(
                'label' => 'Mot de passe oublié ?',
                'route' => 'reset-password/:userId/:token',
                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
                'action'     => 'reset',
            ),
            'forgotpassword' => array(
                'label' => 'Mot de passe oublié ?',
                'route' => 'mot-passe-oublie',
                'controller' => \PlaygroundUser\Controller\Frontend\ForgotController::class,
                'action'     => 'forgot',
            ),
        ),
        'admin' => array(
            'playgrounduser' => array(
                'label' => 'Users',
                'route' => 'admin/playgrounduser/list',
                'resource' => 'user',
                'privilege' => 'list',
                'pages' => array(
                    'list' => array(
                        'label' => 'Users list',
                        'route' => 'admin/playgrounduser/list',
                        'resource' => 'user',
                        'privilege' => 'list',
                    ),
                    'create' => array(
                        'label' => 'Create user',
                        'route' => 'admin/playgrounduser/create',
                        'resource' => 'user',
                        'privilege' => 'add',
                    ),
                    'edit' => array(
                        'label' => 'Edit user',
                        'route' => 'admin/playgrounduser/edit',
                        'privilege' => 'edit',
                    ),
                    'listrole' => array(
                        'label' => 'Roles list',
                        'route' => 'admin/playgrounduser/listrole',
                        'resource' => 'user',
                        'privilege' => 'list',
                    ),
                ),
            ),
        ),
    ),

    'playgrounduser' => array(
        // add default registration role to BjyAuthorize
        'default_register_role' => 'user',
        'user_list_elements' => array(
            'Id' => 'id',
            'Email address' => 'email',
            'Username' => 'username',
            'Firstname' => 'firstname',
            'Lastname' => 'lastname',
            'Telephone' => 'telephone',
            'Mobile' => 'mobile',
        ),
        'create_form_elements' => array(
            // username & password are already added by default form
            'Firstname' => 'firstname',
            'Lastname' => 'lastname',
            'Telephone' => 'Telephone',
            'Mobile' => 'mobile',
        ),
        'edit_form_elements' => array(
            'Username' => 'username',
            'Email' => 'email',
            'Firstname' => 'firstname',
            'Lastname' => 'lastname',
            'Telephone' => 'Telephone',
            'Mobile' => 'mobile',
            //'Created at' => 'createdAt',
            //'Updated at' => 'updatedAt'
        ),
        'new_email_subject_line' => 'your new password',
        //'create_user_auto_password' => true
        'admin' => array(
	        'route_login' => 'admin',
	        'resource' => 'core',
	        'privilege'   => 'dashboard',
	        'controller' => 'PlaygroundDesign\Controller\Dashboard',
	        'action' => 'index',
	        'route_login_fail' => 'admin'
	    )
    )
);
