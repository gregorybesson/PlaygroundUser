<?php

namespace PlaygroundUserTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ForgotControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();

        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );
    }

    public function testForgotPasswordFormAccessed()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        
        //mocking the method cleanExpiredForgotRequests
        $f = $this->getMockBuilder('PlaygroundUser\Service\Password')
        ->setMethods(array('cleanExpiredForgotRequests'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $serviceManager->setService('playgrounduser_password_service', $f);
        
        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('cleanExpiredForgotRequests')
        ->will($this->returnValue(true));
        
        $this->dispatch('/mon-compte/mot-passe-oublie');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName(\PlaygroundUser\Controller\Frontend\ForgotController::class);
        $this->assertControllerClass('ForgotController');
        $this->assertActionName('forgot');
        $this->assertMatchedRouteName('frontend/zfcuser/forgotpassword');
    }

    public function testSentPasswordWrongMail()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        
        //mocking the method cleanExpiredForgotRequests
        $f = $this->getMockBuilder('PlaygroundUser\Service\User')
        ->setMethods(array('getUserMapper'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $serviceManager->setService('playgrounduser_user_service', $f);
        
        $userMock = $this->getMockBuilder('PlaygroundUser\Mapper\User')
        ->disableOriginalConstructor()
        ->getMock();
        
        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('getUserMapper')
        ->will($this->returnValue($userMock));
        
        $userMock->expects($this->any())
        ->method('findById')
        ->will($this->returnValue(false));
        
        $this->dispatch('/mon-compte/envoi-mot-passe/fake.mail@address');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName(\PlaygroundUser\Controller\Frontend\ForgotController::class);
        $this->assertControllerClass('ForgotController');
        $this->assertActionName('sent');
        $this->assertMatchedRouteName('frontend/zfcuser/sentpassword');
    }
}
