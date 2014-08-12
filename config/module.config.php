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
            'user_lib' => array(
                # module root path for your css and js files
                'root_path' => array(
                    __DIR__ . '/../view/lib',
                ),
                # collection of assets
                'collections' => array(
                    'fbregister_js' => array(
                        'assets' => array(
                            'js/fbregister.js',
                        ),
                        'options' => array(
                            'move_raw' => true,
                            'output' => 'lib',
                        )
                    ),
                ),
            ),
        ),
    ),
    
    'bjyauthorize' => array(
        'default_role' => 'guest',
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationDoctrineEntity',
        'role_providers' => array(
            'BjyAuthorize\Provider\Role\Config' => array(
                'guest' => array(),
                'user'  => array('children' => array(
                    'admin' =>  array(),
                ))
            ), 
    
            'BjyAuthorize\Provider\Role\DoctrineEntity' => array(
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
                array('controller' => 'playgrounduser_user',                                   'roles' => array('guest', 'user')),
                array('controller' => 'playgrounduser_forgot',                                 'roles' => array('guest', 'user')),
        
                // Admin area
                array('controller' => 'playgrounduseradmin_login',                             'roles' => array('guest', 'user')),
                array('controller' => 'playgrounduseradmin',                                    'roles' => array('admin')),
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
                        'playgrounduser_user' => array(
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
                        'playgrounduser_forgot' => array(
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
                'type'         => 'phpArray',
                'base_dir'     => __DIR__ . '/../language',
                'pattern'      => '%s.php',
                'text_domain'  => 'playgrounduser'
            ),
        ),
    ),

    'controllers' => array(
        'invokables' => array(
        	'playgrounduseradmin_login' => 'PlaygroundUser\Controller\Admin\LoginController',
            'playgrounduseradmin'       => 'PlaygroundUser\Controller\Admin\AdminController',
            'playgrounduser_user'       => 'PlaygroundUser\Controller\UserController',
            'playgrounduser_forgot'     => 'PlaygroundUser\Controller\ForgotController',
        ),
    ),

    'router' => array(
        'routes' => array(
        	'frontend' => array(
       			'child_routes' => array(
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
		                    'forgotpassword' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/mot-passe-oublie',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_forgot',
		                                'action'     => 'forgot',
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
		                                'controller' => 'playgrounduser_forgot',
		                                'action'     => 'sent',
		                            ),
		                        ),
		                    ),
		                    'ajaxlogin' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajaxlogin',
		                            'defaults' => array(
		                                    'controller' => 'playgrounduser_user',
		                                    'action'     => 'ajaxlogin',
		                            ),
		                        ),
		                    ),
		                    'login' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/login',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
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
		                                        'controller' => 'playgrounduser_user',
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
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'logout',
		                            ),
		                        ),
		                    ),
		                    'ajaxauthenticate' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajaxauthenticate',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
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
		                                'controller' => 'playgrounduser_forgot',
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
		                                'controller' => 'playgrounduser_forgot',
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
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'register',
		                            ),
		                        ),
		                    ),
		                    'registermail' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/registermail',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'registermail',
		                            ),
		                        ),
		                    ),
		                    'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
		                    'backend' => array(
		                        'type' => 'Segment',
		                        'options' => array(
		                            'route' => '/backend',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action' => 'HybridAuthBackend'
		                            )
		                        ),
		                    ),

		                    'profile' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/mes-coordonnees',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'profile',
		                            ),
		                        ),
		                    ),
		                    'profile_prizes' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/prizes',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'prizeCategoryUser',
		                            ),
		                        ),
		                    ),
		                    'newsletter' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/newsletter',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'newsletter',
		                            ),
		                        ),
		                    ),
		                    'ajax_newsletter' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/ajax-newsletter',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'ajaxNewsletter',
		                            ),
		                        ),
		                    ),
		                    'changepassword' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/change-password',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'changepassword',
		                            ),
		                        ),
		                    ),
		                    'blockaccount' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/block-account',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
		                                'action'     => 'blockAccount',
		                            ),
		                        ),
		                    ),
		                    'changeemail' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/change-email',
		                            'defaults' => array(
		                                'controller' => 'playgrounduser_user',
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
           				'controller' => 'playgrounduseradmin_login',
           				'action'     => 'login',
            		),
            	),
                'child_routes' => array(
                    'playgrounduser' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/user',
                            'defaults' => array(
                                'controller' => 'playgrounduseradmin',
                                'action'     => 'index',
                            ),
                        ),
                        'child_routes' =>array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list/:roleId[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgrounduseradmin',
                                        'action'     => 'list',
                                        'roleId' 	 => 'user',
                                        'filter' 	 => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                ),
                                /*'may_terminate' => true,
                                'child_routes' => array(
                                    'pagination' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '[/:p]',
                                            'defaults' => array(
                                                'controller' => 'playgrounduseradmin',
                                                'action'     => 'list',
                                            ),
                                        ),
                                    ),
                                ),*/
                            ),
                            'create' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/create/:userId',
                                    'defaults' => array(
                                        'controller' => 'playgrounduseradmin',
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
                                        'controller' => 'playgrounduseradmin',
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
                                        'controller' => 'playgrounduseradmin',
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
                                        'controller' => 'playgrounduseradmin',
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
                                        'controller' => 'playgrounduseradmin',
                                        'action'     => 'reset',
                                        'userId'     => 0
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
                'controller' => 'playgrounduser_user',
                'action'     => 'register',
            ),
            'profile' => array(
                'label' => 'Modifier mes informations',
                'route' => 'profile',
                'controller' => 'playgrounduser_user',
                'action'     => 'profile',
            ),
            'registermail' => array(
                'label' => 'Inscrivez-vous ou accédez à votre compte',
                'route' => 'registermail',
                'controller' => 'playgrounduser_user',
                'action'     => 'registermail',
            ),

            'newsletter' => array(
                'label' => 'Newsletter',
                'route' => 'frontend/zfcuser/newsletter',
                'controller' => 'playgrounduser_user',
                'action'     => 'newsletter',
            ),
            'resetpassword' => array(
                'label' => 'Mot de passe oublié ?',
                'route' => 'reset-password/:userId/:token',
                'controller' => 'playgrounduser_forgot',
                'action'     => 'reset',
            ),
            'forgotpassword' => array(
                'label' => 'Mot de passe oublié ?',
                'route' => 'mot-passe-oublie',
                'controller' => 'playgrounduser_forgot',
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
    )
);
