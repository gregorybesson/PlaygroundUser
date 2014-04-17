<?php

namespace PlaygroundUserTest\Controller\API;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request as HttpRequest;

class UserControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testCreateAction()
    {
        
        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> ''));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('create');
        $this->assertMatchedRouteName('api/playgrounduser_create');

        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals("3", $content['status']);
        $this->assertEquals("data is required", $content['message']);


        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> 'gffff'));
        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals("4", $content['status']);
        $this->assertEquals("user is not valid", $content['message']);


        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger@adfab.fr"}'));
        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals("5", $content['status']);
        $this->assertEquals("user is already exist", $content['message']);

        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger.test@adfab.fr"}'));
        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals("6", $content['status']);
        $this->assertEquals("user is not valid", $content['message']);


        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger.test@adfab.fr","password":"troger"}'));
        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals("1", $content['status']);
        $this->assertEquals("user is not valid", $content['message']);

 
        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger.google+facebook+twitter@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184", "google" : "1232323", "twitter" : "987654321" }'));
        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("b9f874c73e22bc5a944022b6627c5b7b:2d50cd602d9350f057ca88ca5eabc936b3c0e1b0", $content['token']);

        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger.google+facebook+twitter+role@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "roleId" : "1" }'));
        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("10fafce4494c07f00bad3aff4b4e1831:933539bfd89c6c02a25ab5e02981817ae253a54a", $content['token']);
    }

    public function testDeleteAction()
    {
        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> ''));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("3", $content['status']);
        $this->assertEquals("data is required", $content['message']);

        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> 'dfhfdjfhjdj'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("8", $content['status']);
        $this->assertEquals("token is required", $content['message']);


        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> '{"token":"916dc6827ee96b3210c0e36e53560763:dshjsdhjshdsjdh"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("9", $content['status']);
        $this->assertEquals("user not recognized", $content['message']);


        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> '{"token":"fef3d87f5ee26be982f72a09dff88562:6a4214ba318909a3d58e6cf4d4d2b7437dea0f33"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("9", $content['status']);
        $this->assertEquals("user not recognized", $content['message']);
        

        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> '{"token":"b9f874c73e22bc5a944022b6627c5b7b:2d50cd602d9350f057ca88ca5eabc936b3c0e1b0"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);

        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> '{"token":"10fafce4494c07f00bad3aff4b4e1831:933539bfd89c6c02a25ab5e02981817ae253a54a"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
    }


    public function testloginAction()
    {

        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger.google+facebook+twitter+login@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184", "google" : "1232323", "twitter" : "987654321" }'));
        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("bd059b929ed269abdd3fa35ad74e7288:47b2795cc87b605f11aa3982aa64d06d41e02cc0", $content['token']);

        $this->dispatch('/api/user/logout', HttpRequest::METHOD_POST, array('data'=> 'ddd'));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('logout');
        $this->assertMatchedRouteName('api/playgrounduser_logout');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("8", $content['status']);
        $this->assertEquals("token is required", $content['message']);

        $this->dispatch('/api/user/logout', HttpRequest::METHOD_POST, array('data'=> '{"token":"bd059b929ed269abdd3fa35ad74e7288:47b2795cc87b605f11aa3982aa64d06d41e02cc0"}'));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('logout');
        $this->assertMatchedRouteName('api/playgrounduser_logout');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);


        $this->dispatch('/api/user/login', HttpRequest::METHOD_POST, array('data'=> '{"identity":"troger@adfab.fr", "password" : "troger"}'));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('login');
        $this->assertMatchedRouteName('api/playgrounduser_login');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("1", $content['status']);
        $this->assertEquals("user is not valid", $content['message']);

        $this->dispatch('/api/user/login', HttpRequest::METHOD_POST, array("identity" => "troger@adfab.fr", "credential" =>  "troger"));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('login');
        $this->assertMatchedRouteName('api/playgrounduser_login');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("2", $content['status']);
        $this->assertEquals("user is not valid", $content['message']);

        $this->dispatch('/api/user/login', HttpRequest::METHOD_POST, array("identity" => "thomas.roger.google+facebook+twitter+login@adfab.fr", "credential" => "troger"));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('login');
        $this->assertMatchedRouteName('api/playgrounduser_login');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("bd059b929ed269abdd3fa35ad74e7288:47b2795cc87b605f11aa3982aa64d06d41e02cc0", $content['token']);


        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> '{"token":"bd059b929ed269abdd3fa35ad74e7288:47b2795cc87b605f11aa3982aa64d06d41e02cc0"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
    }



    public function testProfileAction()
    {

        $this->dispatch('/api/user/create', HttpRequest::METHOD_POST, array('data'=> '{"email":"thomas.roger.google+facebook+twitter+profile@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184", "google" : "1232323", "twitter" : "987654321" }'));
        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("7bfff73d8bb610e391a9eaa91eea61c4:5f5789122d9bfaddc94efa204ad4b00836fd0e84", $content['token']);

        $this->dispatch('/api/user/profile', HttpRequest::METHOD_POST, array('data'=> ''));
        $this->assertResponseStatusCode(200);

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("3", $content['status']);
        $this->assertEquals("data is required", $content['message']);


        $this->dispatch('/api/user/profile', HttpRequest::METHOD_POST, array('data'=> '{"token" : "7bfff73d8bb610e391a9eaa91eea61c4:5f5789122d9bfaddc94efa204ad4b00836fd0e84"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('profile');
        $this->assertMatchedRouteName('api/playgrounduser_profile');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("93 Boulevard Decauville", $content['profile']['address']);

        $this->dispatch('/api/user/edit', HttpRequest::METHOD_POST, array('data'=> ''));
        $this->assertResponseStatusCode(200);

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("3", $content['status']);
        $this->assertEquals("data is required", $content['message']);

        $profile = '{"token":"7bfff73d8bb610e391a9eaa91eea61c4:5f5789122d9bfaddc94efa204ad4b00836fd0e84","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "94 Boulevard Decauville", "city": "Evry", "country" : "Fr"}';
        $this->dispatch('/api/user/edit', HttpRequest::METHOD_POST, array('data'=> $profile));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('edit');
        $this->assertMatchedRouteName('api/playgrounduser_edit');
        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("4", $content['status']);
        $this->assertEquals("user is not valid", $content['message']);


        $profile = '{"token":"7bfff73d8bb610e391a9eaa91eea61c4:5f5789122d9bfaddc94efa204ad4b00836fd0e84","email":"thomas.roger.google+facebook+twitter+profile@adfab.fr","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "94 Boulevard Decauville", "city": "Evry", "country" : "Fr"}';
        $this->dispatch('/api/user/edit', HttpRequest::METHOD_POST, array('data'=> $profile));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('edit');
        $this->assertMatchedRouteName('api/playgrounduser_edit');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);

        $this->dispatch('/api/user/profile', HttpRequest::METHOD_POST, array('data'=> '{"token" : "7bfff73d8bb610e391a9eaa91eea61c4:5f5789122d9bfaddc94efa204ad4b00836fd0e84"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('profile');
        $this->assertMatchedRouteName('api/playgrounduser_profile');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
        $this->assertEquals("94 Boulevard Decauville", $content['profile']['address']);


        $this->dispatch('/api/user/delete', HttpRequest::METHOD_POST, array('data'=> '{"token":"7bfff73d8bb610e391a9eaa91eea61c4:5f5789122d9bfaddc94efa204ad4b00836fd0e84"}'));
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_api_user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('delete');
        $this->assertMatchedRouteName('api/playgrounduser_delete');

        $content = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals("0", $content['status']);
        $this->assertEquals("", $content['message']);
    }
}
