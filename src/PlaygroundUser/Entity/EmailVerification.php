<?php

namespace PlaygroundUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="user_signup_email_verification")
 */
class EmailVerification
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="request_key", type="string", length=32, nullable=false)
     */
    protected $request_key;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    protected $email_address;

    /**
     * @ORM\Column(name="request_time", type="datetime", nullable=false)
     */
    protected $request_time;

    public function setRequestKey($key)
    {
        $this->request_key = $key;

        return $this;
    }

    public function getRequestKey()
    {
        return $this->request_key;
    }

    public function generateRequestKey()
    {
        $this->setRequestKey(strtoupper(substr(sha1(
            $this->getEmailAddress() .
            '####' .
            $this->getRequestTime()->getTimestamp()
        ), 0, 15)));
    }

    public function setEmailAddress($email)
    {
        $this->email_address = $email;

        return $this;
    }

    public function getEmailAddress()
    {
        return $this->email_address;
    }

    public function setRequestTime($time)
    {
        if (! $time instanceof \DateTime) {
            $time = new \DateTime($time);
        }
        $this->request_time = $time;

        return $this;
    }

    public function getRequestTime()
    {
        if (! $this->request_time instanceof \DateTime) {
            $this->request_time = new \DateTime('now');
        }

        return $this->request_time;
    }

    public function isExpired()
    {
        $expiryDate = new \DateTime('24 hours ago');

        return $this->getRequestTime() < $expiryDate;
    }
}
