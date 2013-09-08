<?php

namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundUser\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\Mapper\AbstractDbMapper;

class Role extends AbstractDbMapper
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

    public function findByRoleId($role)
    {
        $er = $this->em->getRepository('\PlaygroundUser\Entity\Role');

        return $er->findOneBy(array('roleId' => $role));
    }

    public function findById($id)
    {
        $er = $this->em->getRepository($this->options->getRoleEntityClass());

        return $er->find($id);
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

    public function findAll()
    {
        $er = $this->em->getRepository($this->options->getRoleEntityClass());

        return $er->findAll();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}
