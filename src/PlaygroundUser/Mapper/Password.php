<?php
namespace PlaygroundUser\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use PlaygroundUser\Entity\Password as Model;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Db\Sql\Sql;

class Password extends AbstractDbMapper
{
    protected $tableName         = 'user_password_reset';
    protected $keyField          = 'request_key';
    protected $userField         = 'user_id';
    protected $reqtimeField      = 'request_time';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em      = $em;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('\PlaygroundUser\Entity\Password');
        }

        return $this->er;
    }

    public function remove($passwordModel)
    {
        $this->em->remove($passwordModel);
        $this->em->flush();
    }

    public function findByUserId($userId)
    {
        return $this->getEntityRepository()->findBy(array('user_id'=>$userId));
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findBy($array, $sortArray);
    }

    public function findByRequestKey($key)
    {
        return $this->em->findBy(array('requestKey'=>$key));
    }

    public function cleanExpiredForgotRequests($expiryTime=86400)
    {
        $now = new \DateTime((int) $expiryTime . ' seconds ago');
        $query = $this->em->createQuery(
            'DELETE FROM PlaygroundUser\Entity\Password AS p
                WHERE p.requestTime <= :now '
        );
        $query->setParameter('now', $now);

        $query->execute();

        return true;
    }

    public function cleanPriorForgotRequests($userId)
    {
        $query = $this->em->createQuery(
            'DELETE FROM PlaygroundUser\Entity\Password AS p
                WHERE p.user_id = :userId '
        );
        $query->setParameter('userId', $userId);

        $query->execute();

        return true;
    }

    public function findByUserIdRequestKey($userId, $token)
    {
       return $this->getEntityRepository()->findOneBy(array('user_id'=>$userId, 'requestKey'=>$token));
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    public function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

//     protected function fromRow($row)
//     {
//         if (!$row) return false;
//         $evr = Model::fromArray($row->getArrayCopy());

//         return $evr;
//     }

//     public function toScalarValueArray($passwordModel)
//     {
//         return new \ArrayObject(array(
//             $this->keyField      => $passwordModel->getRequestKey(),
//             $this->userField     => $passwordModel->getUserId(),
//             $this->reqtimeField  => $passwordModel->getRequestTime()->format('Y-m-d H:i:s'),
//         ));
//     }


    public function getTableName() { return $this->tableName; }
    public function getPrimaryKey() { $this->keyField; }
    public function getPaginatorAdapter(array $params) { }
    public function getClassName() { return 'PlaygroundUser\Entity\Password'; }
}
