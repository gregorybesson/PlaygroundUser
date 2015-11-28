<?php
namespace PlaygroundUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="user_password_reset")
 */
class Password
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="request_key", type="string", length=32, nullable=false)
     */
    protected $requestKey;

    /**
     * @var int
     * @ORM\Column(type="integer", length=11, unique=true, nullable=false)
     */
    protected $user_id;

    /**
     * @ORM\Column(name="request_time", type="datetime", nullable=false)
     */
    protected $requestTime;

    public function setRequestKey($key)
    {
        $this->requestKey = $key;

        return $this;
    }

    public function getRequestKey()
    {
        return $this->requestKey;
    }

    public function generateRequestKey()
    {
        $this->setRequestKey(strtoupper(substr(sha1(
            $this->getUserId() .
            '####' .
            $this->getRequestTime()->getTimestamp()
        ), 0, 15)));
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setRequestTime($time)
    {
        if (! $time instanceof \DateTime) {
            $time = new \DateTime($time);
        }
        $this->requestTime = $time;

        return $this;
    }

    public function getRequestTime()
    {
        if (! $this->requestTime instanceof \DateTime) {
            $this->requestTime = new \DateTime('now');
        }

        return $this->requestTime;
    }

    public function validateExpired($resetExpire)
    {
        $expiryDate = new \DateTime($resetExpire . ' seconds ago');

        return $this->getRequestTime() < $expiryDate;
    }
}
