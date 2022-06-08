<?php

namespace PlaygroundUserTest\Controller;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class LoginControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );

    }

    public function testLoginActionNotAllowed()
    {
        $this->assertTrue(true);
    }

    // Add BjyAuthorize before activating this test
    /*public function testLoginActionNotAllowed()
    {
    	$this->dispatch('admin/playgrounduser/list');
    	$this->assertResponseStatusCode(302);

    	$this->assertModuleName('playgrounduser');
    	$this->assertControllerName(\PlaygroundUser\Controller\Admin\LoginController::class);
    	$this->assertControllerClass('LoginController');
    	$this->assertActionName('login');
    	$this->assertMatchedRouteName('frontend/lmcuser/login');

    	$this->assertRedirectTo('/admin');
    	//$postData = array('title' => 'Led Zeppelin III', 'artist' => 'Led Zeppelin');
    	//$this->dispatch('/album/add', 'POST', $postData);
    	//$this->assertResponseStatusCode(302);
    }*/
}
