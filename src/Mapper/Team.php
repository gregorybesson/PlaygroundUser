<?php

namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundUser\Options\ModuleOptions;
use Laminas\Hydrator\HydratorInterface;

class Team
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \PlaygroundUser\Options\ModuleOptions
     */
    protected $options;

    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

    public function findByUser($user)
    {
        return $this->getEntityRepository()->findBy(array('users' => $user));
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($filter, $order = null, $limit = null, $offset = null)
    {
        return $this->getEntityRepository()->findBy($filter, $order, $limit, $offset);
    }

    public function findOneBy($array = array(), $sortBy = array())
    {
        $er = $this->getEntityRepository();

        return $er->findOneBy($array, $sortBy);
    }

    public function insert($entity)
    {
        try {
            $entity = $this->persist($entity);
        } catch (DBALException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        return $entity;
    }

    public function update($entity)
    {
        return $this->persist($entity);
    }

    protected function persist($entity)
    {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (DBALException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        return $entity;
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function getEntityRepository()
    {
        return $this->em->getRepository('\PlaygroundUser\Entity\Team');
    }
}
