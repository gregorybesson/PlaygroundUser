<?php
namespace PlaygroundUser\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundUser\Options\ModuleOptions;
use PlaygroundUser\Entity\EmailVerification as Model;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\EventManager\EventProvider;

class EmailVerification extends EventProvider
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

    public function getEntityRepository()
    {
        return $this->em->getRepository('PlaygroundUser\Entity\EmailVerification');
    }

    public function findByEmail($email)
    {
        $entity = $this->getEntityRepository()->findOneBy(array('email_address' => $email));
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

        return $entity;
    }

    public function findByRequestKey($key)
    {
        $entity = $this->getEntityRepository()->findOneBy(array('request_key' => $key));
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

        return $entity;
    }

    public function cleanExpiredVerificationRequests($expiryTime = 86400)
    {
        $now = new \DateTime((int) $expiryTime . ' seconds ago');
        $dateNow = $now->format('Y-m-d H:i:s');
        
        $query = $this->em->createQuery('DELETE PlaygroundUser\Entity\EmailVerification ev WHERE ev.request_time <= :dateNow');
        $query->setParameter('dateNow', $dateNow);

        return $query->getResult();
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    public function remove(Model $evrModel)
    {
        $this->em->remove($evrModel);
        $this->em->flush();

        return true;
    }
}
