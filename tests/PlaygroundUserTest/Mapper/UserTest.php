<?php

namespace PlaygroundUserTest\Mapper;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\User;
use PlaygroundUser\Entity\Role;

class UserTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $userData;


    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('zfcuser_user_mapper');
        $this->rm = $this->sm->get('playgrounduser_role_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $this->userData = array(
            'username'  => 'troger',
            'email' => 'thomas.roger@adfab.fr',
            'displayName' => 'troger',
            'password' => 'troger',
            'state' => '0',
            'firstname' => 'thomas',
            'lastname' => 'roger',
            'optin' => '1',
            'optinPartner' => '0',
        );

        parent::setUp();
    }


    public function testUser()
    {
        $user = new User;
        foreach ($this->userData as $key => $value) {
            $method = 'set'.ucfirst($key);
            $user->$method($value);
        }

        $user = $this->tm->insert($user);

        $newUser = new User;
        foreach ($this->userData as $key => $value) {
            $method = 'set'.ucfirst($key);
            $newUser->$method($value."2");
        }

        $newUser = $this->tm->insert($newUser);
        $this->assertEquals($this->userData['username'], $user->getUsername());
        
        $searchUser = $this->tm->findByEmail($this->userData['email']);
        $this->assertEquals($this->userData['email'], $searchUser->getEmail());

        $searchUser = $this->tm->findByUsername($this->userData['username']);
        $this->assertEquals($this->userData['username'], $searchUser->getUsername());

        $searchUser = $this->tm->findById($user->getId());
        $this->assertEquals($user->getId(), $searchUser->getId());

        $searchUsers = $this->tm->findByState($this->userData['state']);
        $this->assertEquals(count($searchUsers), 1);

        $title = 'MR';
        $newUser->setTitle($title);
        $newUser = $this->tm->update($newUser);
        $searchUsers = $this->tm->findByTitle($title);
        $this->assertEquals(count($searchUsers), 1);

        $searchUsers = $this->tm->findByOptin(true, false);
        $this->assertEquals(count($searchUsers), 1);

        $searchUsers = $this->tm->findByOptin(false, true);
        $this->assertEquals(count($searchUsers), 1);

        $searchUsers = $this->tm->findByOptin(true, true);
        $this->assertEquals(count($searchUsers), 0);

        $newUser->setOptin(1)
            ->setOptinPartner(1);
        $newUser = $this->tm->update($newUser);
        $searchUsers = $this->tm->findByOptin(true, true);
        $this->assertEquals(count($searchUsers), 1);

        $searchUsers = $this->tm->findOneBy(array('optin' => 1));
        $this->assertEquals(count($searchUsers), 1);

        $searchUsers = $this->tm->findAllBy(array('optin' => 'DESC'));
        $this->assertEquals(count($searchUsers), 2);

        $searchUsers = $this->tm->findAll();
        $this->assertEquals(count($searchUsers), 2);

        $this->tm->activate($newUser);
        $this->tm->activate($user);
        $searchUsers = $this->tm->findByState(1);
        $this->assertEquals(count($searchUsers), 1);


        $role = new Role();
        $role->setRoleId(1);
        $role = $this->tm->insert($role);
        $user->setRoles(array($role));
        $user = $this->tm->update($user);
        $this->assertEquals(count($user->getRoles()), 1);
        $user = $this->tm->clearRoles($user);
        $this->assertEquals(count($user->getRoles()), 0);



        $this->tm->remove($newUser);
        $this->tm->remove($user);

        $searchUsers = $this->tm->findByState(1);
        $this->assertEquals(count($searchUsers), 0);
        $searchUsers = $this->tm->findByState(0);
        $this->assertEquals(count($searchUsers), 2);
    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->rm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
