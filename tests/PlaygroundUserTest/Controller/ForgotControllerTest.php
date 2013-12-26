<?php

namespace PlaygroundUserTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ForgotControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testForgotPasswordFormAccessed()
    {
        $this->dispatch('/mon-compte/mot-passe-oublie');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_forgot');
        $this->assertControllerClass('ForgotController');
        $this->assertActionName('forgot');
        $this->assertMatchedRouteName('frontend/zfcuser/forgotpassword');
    }

    public function testSentPasswordWrongMail()
    {
        $this->dispatch('/mon-compte/envoi-mot-passe/fake-mail-address');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser_forgot');
        $this->assertControllerClass('ForgotController');
        $this->assertActionName('sent');
        $this->assertMatchedRouteName('frontend/zfcuser/sentpassword');
    }
}