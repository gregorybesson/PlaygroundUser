<?php

namespace PlaygroundUser\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use PlaygroundUser\Entity\Role;

/**
 *
 * @author greg
 * Use the command : ./vendor/bin/doctrine-module orm:fixtures:load
 * to install these data into database
 */
class LoadRoleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load Role types
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userRole = new Role();
        $userRole->setRoleId('user');

        $manager->persist($userRole);
        $manager->flush();

        $adminRole = new Role();
        $adminRole->setRoleId('admin');
        $adminRole->setParent($userRole);

        $manager->persist($adminRole);
        $manager->flush();

        $supervisorRole = new Role();
        $supervisorRole->setRoleId('supervisor');
        $supervisorRole->setParent($userRole);

        $manager->persist($supervisorRole);
        $manager->flush();

        $gameManagerRole = new Role();
        $gameManagerRole->setRoleId('game-manager');
        $gameManagerRole->setParent($supervisorRole);

        $manager->persist($gameManagerRole);
        $manager->flush();

        // store reference to admin role for User relation to Role
        $this->addReference('admin-role', $adminRole);
        $this->addReference('supervisor-role', $supervisorRole);
        $this->addReference('game-role', $gameManagerRole);
    }

    public function getOrder()
    {
        return 10;
    }
}
