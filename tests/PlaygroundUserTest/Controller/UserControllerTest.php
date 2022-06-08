<?php

namespace PlaygroundUserTest\Controller;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class UserControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    protected function setUp(): void
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );

        parent::setUp();
    }

    /*public function testAddressActionCanBeAccessed()
    {
    	$this->dispatch('/address');
    	$this->assertResponseStatusCode(200);

    	$this->assertModuleName('playgrounduser\controller\frontend\usercontroller');
    	$this->assertControllerName('application\controller\index');
    	$this->assertControllerClass('AddressController');
    	$this->assertActionName('address');
    	$this->assertMatchedRouteName('frontend/address');
    }*/

    public function testLoginActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/login');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('login');
        $this->assertMatchedRouteName('frontend/lmcuser/login');

        $this->assertRedirectTo('/mon-compte/inscription');
        //$postData = array('title' => 'Led Zeppelin III', 'artist' => 'Led Zeppelin');
        //$this->dispatch('/album/add', 'POST', $postData);
        //$this->assertResponseStatusCode(302);
    }

    /*public function testAjaxloginActionCanBeAccessed()
    {
    	$this->dispatch('/mon-compte/ajaxlogin');
    	$this->assertResponseStatusCode(200);

    	$this->assertModuleName('playgrounduser');
    	$this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
    	$this->assertControllerClass('UserController');
    	$this->assertActionName('ajaxlogin');
    	$this->assertMatchedRouteName('frontend/lmcuser/ajaxlogin');
    }*/

    /*public function testAjaxauthenticateActionCanBeAccessed()
    {
    	$this->dispatch('/mon-compte/ajaxauthenticate');
    	$this->assertResponseStatusCode(200);

    	$this->assertModuleName('playgrounduser');
    	$this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
    	$this->assertControllerClass('UserController');
    	$this->assertActionName('ajaxauthenticate');
    	$this->assertMatchedRouteName('frontend/lmcuser/ajaxauthenticate');
    }*/

    public function testLogoutActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/logout');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('logout');
        $this->assertMatchedRouteName('frontend/lmcuser/logout');

        $this->assertRedirectTo('/user/login');
    }

    /*public function testProviderLoginActionCanBeAccessed()
    {
    	$this->dispatch('/mon-compte/login/facebook');
    	$this->assertResponseStatusCode(302);

    	$this->assertModuleName('playgrounduser');
    	$this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
    	$this->assertControllerClass('UserController');
    	$this->assertActionName('providerLogin');
    	$this->assertMatchedRouteName('frontend/lmcuser/login/provider');

    	$this->assertRedirectTo('/mon-compte/inscription');
    }*/

    public function testRegisterActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/inscription');
        // TODO: Fix the error 500
        //$this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('register');
        $this->assertMatchedRouteName('frontend/lmcuser/register');
    }

    public function testProfileActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/mes-coordonnees');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('profile');
        $this->assertMatchedRouteName('frontend/lmcuser/profile');

        $this->assertRedirectTo('/user');
    }

    public function testBlockAccountActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/block-account');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('blockAccount');
        $this->assertMatchedRouteName('frontend/lmcuser/blockaccount');

        $this->assertRedirectTo('/mon-compte/mes-coordonnees');
    }

    public function testNewsletterActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/newsletter');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('newsletter');
        $this->assertMatchedRouteName('frontend/lmcuser/newsletter');
    }

    public function testAjaxNewsletterActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/ajax-newsletter');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('ajaxNewsletter');
        $this->assertMatchedRouteName('frontend/lmcuser/ajax_newsletter');
    }

    public function testPrizeCategoryUserActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/prizes');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('prizeCategoryUser');
        $this->assertMatchedRouteName('frontend/lmcuser/profile_prizes');
    }

    public function testRegistermailActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/registermail');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('registermail');
        $this->assertMatchedRouteName('frontend/lmcuser/registermail');
    }

    public function testChangeemailActionCanBeAccessed()
    {
        $this->dispatch('/mon-compte/change-email');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('playgrounduser');
        $this->assertControllerName('playgrounduser\controller\frontend\usercontroller');
        $this->assertControllerClass('UserController');
        $this->assertActionName('changeemail');
        $this->assertMatchedRouteName('frontend/lmcuser/changeemail');

        $this->assertRedirectTo('/user');
    }
}
