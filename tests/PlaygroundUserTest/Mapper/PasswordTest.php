<?php

namespace PlaygroundUserTest\Mapper;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $passwordData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgrounduser_password_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $this->passwordData = array(
            'user_id'  => '1',
            'requestTime' => 'now',
        );
        parent::setUp();
    }

    public function testCanInsertNewRecord()
    {
        $password = new Password();
        $password->setUserId($this->passwordData['user_id']);
        $password->setRequestTime($this->passwordData['requestTime']);

        $passwordEntityMocked = $this->getMockBuilder('PlaygroundUser\Entity\Password')
            ->setMethods(array('generateRequestKey'))
            ->disableOriginalConstructor()
            ->getMock();


        $return = strtoupper(substr(sha1(
            $this->passwordData['user_id'] .
            '####' .
            $this->passwordData['requestTime']
        ), 0, 15));

        $passwordEntityMocked->expects($this->any())
            ->method('generateRequestKey')
            ->will($this->returnValue($return));

        $password->setRequestKey($passwordEntityMocked->generateRequestKey());

        $password = $this->tm->persist($password);

        $this->assertEquals($this->passwordData['user_id'], $password->getUserId());
        $this->assertInstanceOf('\PlaygroundUser\Entity\Password', $this->tm->findByUserIdRequestKey($this->passwordData['user_id'], $return));
        $this->assertCount(1, $this->tm->findByRequestKey($return));
        $this->tm->remove($password);
        $this->assertNull($this->tm->findByUserIdRequestKey($this->passwordData['user_id'], $return));
        $this->assertEmpty($this->tm->findByRequestKey($return));
    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
