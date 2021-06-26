<?php

namespace PlaygroundUserTest\Service;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\User;

class ProspectTest extends \PHPUnit\Framework\TestCase
{
    protected $traceError = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sm = Bootstrap::getServiceManager();
    }

    public function testFindUserOrCreateByEmail()
    {
        $userService = $this->sm->get('playgrounduser_user_service');
        $email = 'thomas.roger@adfab.fr';
        $user = new User();
        $user->setEmail($email);

        $mapper = $this->getMockBuilder('PlaygroundUser\Mapper\User')
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->any())
            ->method('insert')
            ->will($this->returnValue($user));

        $userService->setUserMapper($mapper);

        $users = $userService->findUserOrCreateByEmail($email);
        $this->assertEquals(count([$users]), 1);

        // $mapper->expects($this->any())
        //     ->method('findBy')
        //     ->will($this->returnValue(array($user)));

        // $userService->setUserMapper($mapper);

        // $users = $userService->findUserOrCreateByEmail($email);
        // $this->assertEquals(count($users), 1);
    }
}
