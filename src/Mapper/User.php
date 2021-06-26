<?php

namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcUser\Mapper\User as ZfcUserMapper;
use PlaygroundUser\Options\ModuleOptions;
use Laminas\Hydrator\HydratorInterface;

class User extends ZfcUserMapper
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

    public function findByEmail($email)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findOneBy(array('email' => $email));
    }

    public function findByUsername($username)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findOneBy(array('username' => $username));
    }

    public function findById($id)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->find($id);
    }

    public function findByState($state)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findBy(array('state' => $state));
    }

    public function findByTitle($title)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findBy(array('title' => $title));
    }

    public function findByOptin($optin, $partner)
    {
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        if ($partner == true) {
            if ($optin == true) {
                return $er->findBy(array('optinPartner' => $optin, 'optin' => $optin));
            }
            return $er->findBy(array('optinPartner' => $optin));
        } else {
            return $er->findBy(array('optin' => $optin));
        }
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

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    public function clearRoles($entity)
    {
        $entity->setRoles(null);
        //$entity->getRoles()->clear();

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
        $er = $this->em->getRepository($this->options->getUserEntityClass());

        return $er->findAll();
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

    public function activate($entity)
    {
        $entity->setState(1);
        $this->em->persist($entity);
        $this->em->flush();
    }
}
