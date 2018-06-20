<?php
namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use Hybrid_User_Profile;
use PlaygroundUser\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\Mapper\AbstractDbMapper;
use ZfcUser\Entity\UserInterface;
use PlaygroundUser\Entity\UserProvider as UserProviderEntity;

class UserProvider extends AbstractDbMapper
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @param EntityManager $em
     * @param ModuleOptions $options
     */
    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

    /**
     * @param  string        $providerId
     * @param  string        $provider
     * @return UserInterface
     */
    public function findUserByProviderId($providerId, $provider)
    {
        $er = $this->getEntityRepository();
        $entity = $er->findOneBy(array('providerId' => $providerId, 'provider' => $provider));

        return $entity;
    }

    /**
     * @param UserInterface       $user
     * @param Hybrid_User_Profile $hybridUserProfile
     * @param string              $provider
     * @param array               $accessToken
     */
    public function linkUserToProvider(UserInterface $user, Hybrid_User_Profile $hybridUserProfile, $provider, array $accessToken = null)
    {
        $userProvider = $this->findUserByProviderId($hybridUserProfile->identifier, $provider);

        if (false != $userProvider) {
            if ($user->getId() == $userProvider->getUser()->getId()) {
                // already linked
                return;
            }
            throw new \RuntimeException('This ' . ucfirst($provider) . ' profile is already linked to another user.');
        }

        $userProvider = new UserProviderEntity;
        $userProvider->setUser($user)
                     ->setProviderId($hybridUserProfile->identifier)
                     ->setProvider($provider);
        $this->insert($userProvider);
    }

    /**
     * @param  UserInterface     $entity
     * @param  string            $tableName
     * @param  HydratorInterface $hydrator
     * @return UserInterface
     */
    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    /**
     * @param  UserInterface     $entity
     * @param  string            $tableName
     * @param  HydratorInterface $hydrator
     * @return UserInterface
     */
    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    /**
     * @param  UserInterface $entity
     * @return UserInterface
     */
    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
     * @param  UserInterface               $user
     * @param  string                      $provider
     * @return UserProviderInterface|false
     */
    public function findProviderByUser(UserInterface $user, $provider)
    {
        $er = $this->getEntityRepository();
        $entity = $er->findOneBy(array('user' => $user, 'provider' => $provider));
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

        return $entity;
    }

    /**
     * @param  UserInterface $user
     * @return array
     */
    public function findProvidersByUser(UserInterface $user)
    {
        $er = $this->getEntityRepository();
        $entities = $er->findBy(array('user' => $user));

        $return = array();
        foreach ($entities as $entity) {
            $return[$entity->getProvider()] = $entity;
            $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        }

        return $return;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundUser\Entity\UserProvider');
        }

        return $this->er;
    }

     /**
    * remove : supprimer une entite userProvider
    * @param UserProvider
    *
    */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}
