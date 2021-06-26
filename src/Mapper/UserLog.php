<?php

namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcUser\Mapper\User as ZfcUserMapper;
use PlaygroundUser\Options\ModuleOptions;
use Laminas\Hydrator\HydratorInterface;

class UserLog extends ZfcUserMapper
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

    public function findById($id)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->find($id);
    }

    public function findOneBy($array)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findOneBy($array);
    }

    public function findAllBy($sortArray = array())
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findBy(array(), $sortArray);
    }

    public function findAll()
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findAll();
    }

    public function removeAll($userId)
    {
        $elements = $this->findBy(array('user_id' => $userId));
        foreach ($elements as $element) {
            $this->em->remove($element);
        }
        $this->em->flush();
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
     * We don't delete the user, but just disable it
     * @param unknown_type $entity
     */
    public function remove($entity)
    {
        $entity->setState(0);
        $this->em->persist($entity);
        $this->em->flush();
    }
}
