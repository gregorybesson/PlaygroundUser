<?php

namespace PlaygroundCore\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use PlaygroundUser\Entity\Role;

/**
 *
 * @author greg
 * Use the command : php doctrine-module.php data-fixture:import --append
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

        // store reference to admin role for User relation to Role
        $this->addReference('admin-role', $adminRole);
    }

    public function getOrder()
    {
        return 10;
    }
}
