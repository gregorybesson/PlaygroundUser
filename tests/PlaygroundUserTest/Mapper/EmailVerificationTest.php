<?php

namespace PlaygroundUserTest\Mapper;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\EmailVerification;

class EmailVerificationTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $emailData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgrounduser_emailverification_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $this->emailData = array(
            'email_address'  => 'thomas.roger@adfab.fr',
            'request_time' => 'now',
        );


        parent::setUp();
    }

    

    public function testCanInsertNewRecord()
    {
        $email = new EmailVerification();
        $email->setRequestTime($this->emailData['request_time'])
            ->setEmailAddress($this->emailData['email_address']);

        $emailEntityMocked = $this->getMockBuilder('PlaygroundUser\Entity\EmailVerification')
            ->setMethods(array('generateRequestKey'))
            ->disableOriginalConstructor()
            ->getMock();
        

        $return = strtoupper(substr(sha1(
            $this->emailData['email_address'] .
            '####' .
            $this->emailData['request_time']
        ), 0, 15));

        $emailEntityMocked->expects($this->any())
            ->method('generateRequestKey')
            ->will($this->returnValue($return));

        $email->setRequestKey($emailEntityMocked->generateRequestKey());

        $email = $this->tm->insert($email);
   
        $this->assertEquals($this->emailData['email_address'], $email->getEmailAddress());

        
        $emailVerification = $this->tm->findByEmail($this->emailData['email_address']);
        $this->assertEquals("object", gettype($emailVerification));
        $this->assertEquals("PlaygroundUser\Entity\EmailVerification", get_class($emailVerification));
        $this->assertEquals($email->getRequestKey(), $emailVerification->getRequestKey());

        $emailVerification = $this->tm->findByRequestKey($emailEntityMocked->generateRequestKey());
        $this->assertEquals("object", gettype($emailVerification));
        $this->assertEquals("PlaygroundUser\Entity\EmailVerification", get_class($emailVerification));
        $this->assertEquals($email->getRequestKey(), $emailVerification->getRequestKey());

        $this->tm->remove($email);
        $emailVerification = $this->tm->findByRequestKey($emailEntityMocked->generateRequestKey());
        $this->assertEquals(null, $emailVerification);
    }

    public function testCleanExpiredVerificationRequests()
    {
        $date = "2011-11-11 11:11:11";
        $email = new EmailVerification();
        $email->setRequestTime($date)
            ->setEmailAddress($this->emailData['email_address']);

        $emailEntityMocked = $this->getMockBuilder('PlaygroundUser\Entity\EmailVerification')
            ->setMethods(array('generateRequestKey'))
            ->disableOriginalConstructor()
            ->getMock();
        

        $return = strtoupper(substr(sha1(
            $this->emailData['email_address'] .
            '####' .
            $date
        ), 0, 15));

        $emailEntityMocked->expects($this->any())
            ->method('generateRequestKey')
            ->will($this->returnValue($return));

        $email->setRequestKey($emailEntityMocked->generateRequestKey());

        $email = $this->tm->insert($email);
        
        $emailVerification = $this->tm->findByRequestKey($emailEntityMocked->generateRequestKey());
        $this->assertEquals("object", gettype($emailVerification));
        $this->assertEquals("PlaygroundUser\Entity\EmailVerification", get_class($emailVerification));
        $this->assertEquals($email->getRequestKey(), $emailVerification->getRequestKey());
        
        $this->tm->cleanExpiredVerificationRequests();

        $emailVerification = $this->tm->findByRequestKey($emailEntityMocked->generateRequestKey());
        $this->assertEquals(null, $emailVerification);
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
