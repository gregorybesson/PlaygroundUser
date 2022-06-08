<?php
return array(
    'modules' => array(
        'DoctrineModule',
        'DoctrineORMModule',
        'LmcUser',
        'PlaygroundCore',
        'PlaygroundDesign',
        'PlaygroundUser',
        'Laminas\Router',
        'Laminas\I18n',
        'Laminas\Form',
        'Laminas\Mvc\Plugin\FlashMessenger',
        'Laminas\Mvc\Plugin\FilePrg',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            '../../../config/autoload/{,*.}{global,local,testing}.php',
            './config/{,*.}{testing}.php',
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
);
