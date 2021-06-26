<?php

namespace PlaygroundUserTest\Mapper;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\Role;

class RoleTest extends \PHPUnit\Framework\TestCase
{
    protected $traceError = true;

    protected $roleData;


    protected function setUp(): void
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgrounduser_role_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $this->roleData = array(
            'roleId'  => '1',
        );


        parent::setUp();
    }


    public function testRole()
    {
        $role = new Role();
        $role->setRoleId($this->roleData['roleId']);
        $role = $this->tm->insert($role);
   
        $this->assertEquals($this->roleData['roleId'], $role->getRoleId());

        $childRole = new Role();
        $childRole->setRoleId('2')
            ->setParent($role);
        $childRole = $this->tm->insert($childRole);
   
        $this->assertEquals($childRole->getParent(), $role);
        $newRoleId = 3;
        $childRole->setRoleId($newRoleId);
        $childRole = $this->tm->update($childRole);
        $this->assertEquals($childRole->getRoleId(), $newRoleId);

        $roles = $this->tm->findById($childRole->getId());
        $this->assertEquals($roles, $childRole);

        $roles = $this->tm->findByRoleId($this->roleData['roleId']);
        $this->assertEquals($roles, $role);

        $roles = $this->tm->findAll();
        $this->assertEquals(count($roles), 2);

        $this->tm->remove($childRole);
        $roles = $this->tm->findAll();
        $this->assertEquals(count($roles), 1);
    }
 

    protected function tearDown(): void
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
