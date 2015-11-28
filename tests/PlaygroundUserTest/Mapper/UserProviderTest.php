<?php

namespace PlaygroundUserTest\Mapper;

use PlaygroundUserTest\Bootstrap;
use PlaygroundUser\Entity\UserProvider;
use PlaygroundUser\Entity\User;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $userData;
    protected $userProvider;


    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->um = $this->sm->get('zfcuser_user_mapper');
        $this->tm = $this->sm->get('playgrounduser_userprovider_mapper');
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
            'title' => 'MR',
            'firstname' => 'thomas',
            'lastname' => 'roger',
            'optin' => '1',
            'optinPartner' => '0',
        );

        $this->userProvider = array(
            'providerId' => '1',
            'provider' => 'provider');

        parent::setUp();
    }


    public function testUserProvider()
    {
        $user = new User;
        foreach ($this->userData as $key => $value) {
            $method = 'set'.ucfirst($key);
            $user->$method($value);
        }

        $user = $this->um->insert($user);

        $userProvider = new UserProvider;
        $userProvider->setUser($user)
            ->setProviderId($this->userProvider['providerId'])
            ->setProvider($this->userProvider['provider']);
        $userProvider = $this->tm->insert($userProvider);
        $this->assertEquals($this->userProvider['provider'], $userProvider->getProvider());

        $newProvider = "provider2";
        $userProvider->setProvider($newProvider);
        $userProvider = $this->tm->update($userProvider);
        $this->assertEquals($newProvider, $userProvider->getProvider());


        $users = $this->tm->findUserByProviderId($userProvider->getProviderId(), $userProvider->getProvider());
        $this->assertEquals(1, count($users));

        $providers = $this->tm->findProvidersByUser($user);
        $this->assertEquals(1, count($providers));

        $providers = $this->tm->findProviderByUser($user, $userProvider->getProvider());
        $this->assertEquals(1, count($providers));

        $this->tm->remove($userProvider);

        $providerName = "providerTest";
        $identifier = "toto";
        $hybridUserProfileMocked = $this->getMockBuilder('Hybrid_User_Profile')
            ->disableOriginalConstructor()
            ->getMock();
        $hybridUserProfileMocked->identifier = $identifier;
        
        $providers = $this->tm->findProviderByUser($user, $providerName);
        $this->assertEquals(0, count($providers));
        $this->tm->linkUserToProvider($user, $hybridUserProfileMocked, $providerName);

        $providers = $this->tm->findProviderByUser($user, $providerName);
        $this->assertEquals(1, count($providers));
       
        $this->tm->linkUserToProvider($user, $hybridUserProfileMocked, $providerName);
        $providers = $this->tm->findProviderByUser($user, $providerName);
        $this->assertEquals(1, count($providers));
        
        $this->tm->remove($providers);
        $this->tm->linkUserToProvider($user, $hybridUserProfileMocked, $providerName);
        $providers = $this->tm->findProviderByUser($user, $providerName);
        $this->assertEquals(1, count($providers));

    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->um);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
