PlaygroundUser
=========

[![Develop Branch Build Status](https://travis-ci.org/gregorybesson/PlaygroundUser.svg)](http://travis-ci.org/gregorybesson/PlaygroundUser)

#Introduction

Ce module étend ZfcUser qui permet de nombreuses fonctionnalités liées à la gestion d'un compte client.

Le fonctionnalités apportées par AdfabUser sont :
* Gestion des autorisations via BjyAuthorize
* Mot de passe oublié
* Activation de compte par mail
* Remember me
* Gestion d'un avatar
* Authentification Facebook (basée sur HybridAuth)
* Login via Ajax
* Gestion de son profil par lutilisateur
* Gestion des comptes en back-office
* Blocage de son compte (== soft delete)

#Installation
## Utilisation de Doctrine
Se positionner via shell dans le répertoire vendor/doctrine/doctrine-module/bin.

La commande php doctrine-module.php orm:schema-tool:create permet d'installer les tables de ce module dans la base de données

La commande php doctrine-module.php data-fixture:import --append permet d'installer les rôles 'user' et 'admin' ainsi que l'utilisateur 'admin@test.com' (mot de passe 'admin') avec les droits d'administration.

#Extending AdfabUser
## Use your own User entity
If you want to use your own entity :

1. Change the value of 'user_entity_class' in the zfcuser.global.php file.

        'user_entity_class' => 'MyUser\Entity\User',

2. Put your doctrine definition in your module.config.php Module (the one extending the user entity)

        'doctrine' => array(
		'driver' => array(
			'zfcuser_entity' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => __DIR__ . '/../src/MyUser/Entity'
			),

			'orm_default' => array(
				'drivers' => array(
				    'MyUser\Entity'  => 'zfcuser_entity'
				)
			)
		)
	    ),

3. Create your entity (using the doctrine annotation) in your Module. You'll have to implement the interface AdfabUser\Entity\UserInterface (see below for the explanation)

        class User implements \AdfabUser\Entity\UserInterface, ProviderInterface, InputFilterAwareInterface

4. The entities of other Adfab modules which need to link AdfabUser entity base the relationship on an interface : AdfabUser\Entity\UserInterface. So that, in case you extend the User entity, you can replace this relationship easily. To be able to do that, your user entity needs to implement AdfabUser\Entity\UserInterface.
And in your Module.php onBootstrap method, You have then to use the doctrine listener feature to replace the interface with the correct class

        public function onBootstrap($e)
        {
    	$sm = $e->getApplication()->getServiceManager();
    	$doctrine = $sm->get('application_doctrine_em');
    	$evm = $doctrine->getEventManager();
	    
	    $listener = new  \Doctrine\ORM\Tools\ResolveTargetEntityListener();
	    $listener->addResolveTargetEntity(
    		'AdfabUser\Entity\UserInterface',
    		'MyUser\Entity\User',
    		array()
	    );
	    $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $listener);
        }

**BEWARE : Your Module extending the entity must be placed after the other modules that have a link to the entity extended in application.config.php**

## Use your own Form
If you want to change the ChangeInfo Form for example (ie. you want to add a 'children' select list to persist in your user database table). First extend the entity as explained previously. Then :

1. Create the Form class in your Module, which extends the AdfabUser Form

        class ChangeInfo extends \AdfabUser\Form\ChangeInfo

    If you want to use the default fields of the AdfabUser Form, you can do this by using the parent constructor

         parent::__construct($name, $createOptions, $translator);

    And then you add the Form elements you want 

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'children',
            'attributes' =>  array(
                'id' => 'children',
                'options' => array(
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('Select', 'adfabuser'),
                'label' => $translator->translate('Children', 'adfabuser'),
            ),
        ));

2. Declare your Form in your Module.php factories definition by reusing the same form name used in AdfabUser (as your module is loaded after AdfabUser, your definition will be taken instead of the AdfabUser definition)

        public function getServiceConfig()
        {
    	return array(
    		'factories' => array(
    			'adfabuser_change_info_form' => function($sm) {
    				$translator = $sm->get('translator');
    				$options = $sm->get('adfabuser_module_options');
    				$form = new Form\ChangeInfo(null, $options, $translator);
   					return $form;
   				},
    		)
    	);
        }

## Use your own User controller
If you want to add an action or modify an existing one.

1. Create the controller in your Module (extend the AdfabUser one if you want to use its methods)

        class UserController extends \AdfabUser\Controller\UserController
        {
            public function profileAction ()
            {
                ...
            }
        }

2. Define your controller and associate the route with your controller in your module.config.php file. Don't forget to define the view name and adjust the core layout definition if you need to

        'core_layout' => array(
    	'MyUser' => array(
    		'default_layout' => 'layout/2columns-left',
    		'children_views' => array(
   			'col_left'  => 'adfab-user/layout/col-user.phtml',
   		),
        ),						
        ),		
        'controllers' => array(
		'invokables' => array(
			'myuser_user'    => 'MyUser\Controller\UserController',
		),
        ),	
        'router' => array(
		'routes' => array(
			'zfcuser' => array(
				'child_routes' => array(
					'profile' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/mes-coordonnees',
							'defaults' => array(
								'controller' => 'myuser_user',
								'action'     => 'profile',
							),
						),
					),
				),
			),
		),
        ),
        'view_manager' => array(
        'template_path_stack' => array(
            'adfabuser' => __DIR__ . '/../view',
        ),
        'template_map' => array(
            'adfab-user/header/login.phtml' => __DIR__ . '/../view/adfab-user/frontend/header/login.phtml',
            'my-user/user/profile'       => __DIR__ . '/../view/adfab-user/frontend/account/profile.phtml',
        ),
        ),

3. You'll have to declare this new controller to bjyauthorize.global.php

        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
            	//Front Area
            	array('controller' => 'myuser_user', 'roles' => array('guest', 'user')),
