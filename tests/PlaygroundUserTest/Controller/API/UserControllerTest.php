<?php

namespace PlaygroundUserTest\Controller\API;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

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

    public function testloginAction()
    { }

    public function testLogoutAction()
    { }

     /**
    * POST
    *
    * {"email":"thomas.roger@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr"}
    * {"email":"thomas.roger.facebook@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184"}
    * {"email":"thomas.roger.twitter@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "twitter" : "18410400"}
    * {"email":"thomas.roger.google@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "google" : "105792242901994540498"}
    * {"email":"thomas.roger.google+facebook@adfab.fr","password":"troger","passwordVerify":"troger","firstname": "Thomas", "lastname": "ROGER", "postalCode": "91000", "dob": "07/04/1987","address": "93 Boulevard Decauville", "city": "Evry", "country" : "Fr", "facebook" : "664427184", "google" : "1232323"}
    *
    */
    public function testCreateAction()
    {

    }

    public function testProfileAction()
    { }

    public function testEditAction()
    {}

    public function testDeleteAction()
    {}

    public function testCheckUser()
    {}
}
