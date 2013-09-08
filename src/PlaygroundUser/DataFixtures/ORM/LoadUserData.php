<?php

namespace AdfabCore\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use PlaygroundUser\Entity\User;
use Zend\Crypt\Password\Bcrypt;

/**
 *
 * @author greg
 * Use the command : php doctrine-module.php data-fixture:import --append
 * to install these data into database
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load address types
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setFirstname('admin');
        $user->setFirstname('admin');
        $user->setUsername('admin');
        $user->setEmail('admin@test.com');
        $user->setState(1);

        $newPass = 'playground';

        $bcrypt = new Bcrypt;
        $bcrypt->setCost(14);

        $pass = $bcrypt->create($newPass);
        $user->setPassword($pass);

        $user->addRole(
			$this->getReference('admin-role') // load the stored reference
        );

        $manager->persist($user);

        $manager->flush();
    }

    public function getOrder()
    {
        return 50;
    }
}
