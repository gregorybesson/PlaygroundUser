<?php

namespace PlaygroundUserTest\Entity;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\User;

class userTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Service Manager
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;

    /**
     * Doctrine Entity Manager
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * User sample
     * @var Array
     */
    protected $userData;

    public function setUp()
    {
        parent::setUp();

        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');

        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();

        $tool->dropSchema($classes);

        $tool->createSchema($classes);

        $this->userData = array(
        	'email' => 'correct@test.com',
        	'firstname' => 'Jo',
       		'lastname'  => 'Lindien',
       		'telephone' => '0123456789',
        	'mobile' => '0623456789',
        	'avatar' => 'correct@test.com',
        	'state' => '1',
       		'postalCode'  => '77000',
       		'optin' => '1',
        	'optinPartner' => '1',
        	'username'  => 'jolindien',
        );
    }

    public function testCanInsertNewRecord()
    {
        $user = new User();
        $user->populate($this->userData);
        $rand = \Zend\Math\Rand::getString(8);
        $bcrypt = new \Zend\Crypt\Password\Bcrypt;
        $bcrypt->setCost(6);
        $pass = $bcrypt->create($rand);
        $user->setPassword($pass);
        // save data
        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals($this->userData['email'], $user->getEmail());

        return $user->getId();
    }

    public function testCanInsertNewRecordUKZipCode()
    {
        $this->userData['postalCode'] = 'AA9A 9AA';
        $user = new User();
        $user->populate($this->userData);
        $rand = \Zend\Math\Rand::getString(8);
        $bcrypt = new \Zend\Crypt\Password\Bcrypt;
        $bcrypt->setCost(6);
        $pass = $bcrypt->create($rand);
        $user->setPassword($pass);
        // save data
        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals($this->userData['email'], $user->getEmail());

        return $user->getId();
    }

    public function testCanInsertNewRecordUSZipCode()
    {
        $this->userData['postalCode'] = '12345-6789';
        $user = new User();
        $user->populate($this->userData);
        $rand = \Zend\Math\Rand::getString(8);
        $bcrypt = new \Zend\Crypt\Password\Bcrypt;
        $bcrypt->setCost(6);
        $pass = $bcrypt->create($rand);
        $user->setPassword($pass);
        // save data
        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals($this->userData['email'], $user->getEmail());

        return $user->getId();
    }


    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanUpdateInsertedRecord($id)
    {
        $data = array(
            'id' => $id
        );
        $user = $this->em->getRepository('PlaygroundUser\Entity\User')->find($id);
        $this->assertInstanceOf('PlaygroundUser\Entity\User', $user);
        $this->assertEquals($this->userData['email'], $user->getEmail());

        $user->populate($data);
        $this->em->flush();

        $this->assertEquals($this->userData['email'], $user->getEmail());
        $this->assertEquals($this->userData['firstname'], $user->getFirstname());
        $this->assertEquals($this->userData['lastname'], $user->getLastname());
        $this->assertEquals($this->userData['telephone'], $user->getTelephone());
        $this->assertEquals($this->userData['mobile'], $user->getMobile());
        $this->assertEquals($this->userData['avatar'], $user->getAvatar());
        $this->assertEquals($this->userData['postalCode'], $user->getPostalCode());
        $this->assertEquals($this->userData['state'], $user->getState());
        $this->assertEquals($this->userData['username'], $user->getUsername());
        $this->assertEquals($this->userData['optin'], $user->getOptin());
        $this->assertEquals($this->userData['optinPartner'], $user->getOptinPartner());
    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanRemoveInsertedRecord($id)
    {
        $user = $this->em->getRepository('PlaygroundUser\Entity\User')->find($id);
        $this->assertInstanceOf('PlaygroundUser\Entity\User', $user);

        $this->em->remove($user);
        $this->em->flush();

        $user = $this->em->getRepository('PlaygroundUser\Entity\User')->find($id);
        $this->assertEquals(false, $user);
    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        //$result = $dbh->exec("UPDATE sqlite_sequence SET seq = 10 WHERE name='album';");

        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
