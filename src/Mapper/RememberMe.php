<?php

namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundUser\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;


class RememberMe
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
        return $this->getRepository()->find($id);
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getRepository()->findBy($array, $sortArray);
    }

    public function findByIdSerie($userId, $serieId)
    {
        return $this->getRepository()->findOneBy(array('userId' => $userId, 'sid' => $serieId));
    }

    public function removeAll($userId)
    {
        $elements = $this->findBy(array('userId' => $userId));
        foreach ($elements as $element) {
            $this->em->remove($element);
        }
        $this->em->flush();
    }

    public function removeSerie($userId, $serieId)
    {
        $elements = $this->findBy(array('userId' => $userId, 'sid' => $serieId));
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

    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getRepository()
    {
        return $this->em->getRepository('\PlaygroundUser\Entity\RememberMe');
    }
}
