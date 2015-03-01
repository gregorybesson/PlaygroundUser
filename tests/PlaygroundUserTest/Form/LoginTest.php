<?php

namespace PlaygroundUserTest\Form;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\User;
use PlaygroundUser\Form\Login;

class LoginTest extends \PHPUnit_Framework_TestCase
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
     * Entity instance
     * @var Loculus\Entity
     */
    protected $entity;

    /**
     * Form instance
     * @var Zend\Form\Form
     */
    protected $form;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->entity = new User();
        $translator = $this->sm->get('translator');
        $options = $this->sm->get('zfcuser_module_options');
        $form = new \PlaygroundUser\Form\Login(null, $options, $translator);
        $form->setInputFilter(new \PlaygroundUser\Form\LoginFilter($options));

        $this->form = $form;

        parent::setUp();
    }

    public function testCanInsertNewRecord()
    {
        $data = array(
            'identity' => 'jo@test.com',
            'credential' => 'aaaaaa'
        );
        $this->form->setData($data);
        $this->assertTrue($this->form->isValid());
    }

    /*public function testCannotInsertNewRecordWithInvalidData()
    {
        $data = array(
            'identity' => 'jo@test.com',
            'credential' => '',
        );
        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(1, count($this->form->getMessages()));
    }*/

    /*public function testCanUpdateExistingRecord()
    {
        $user = $this->em->find('PlaygroundUser\Entity\User', 8);
        $data = array(
            'email' => 'jean@test.com',
            'firstname' => 'Jean',
            'lastname' => 'Quirit'
        );
        $this->form->setData($data);
        $this->assertTrue($this->form->isValid());
    }

    public function testCannnotUpdateExistingRecordWithInvalidData()
    {
        $user = $this->em->find('PlaygroundUser\Entity\User', 8);
        $data = array(
            'email' => 'jean@test.com',
            'firstname' => 'Jean',
            'lastname' => 'Quirit'
        );
        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(1, count($this->form->get('email')->getMessages()));
    }*/

    public function tearDown()
    {
        unset($this->sm);
        unset($this->em);
        unset($this->entity);
        unset($this->form);

        parent::tearDown();
    }
}
