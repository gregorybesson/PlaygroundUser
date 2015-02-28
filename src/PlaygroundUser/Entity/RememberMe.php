<?php
namespace PlaygroundUser\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="user_remember_me")
 */
class RememberMe
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="sid");
     */
    protected $sid;

    /** 
     * @ORM\Id
     * @ORM\Column(type="string") 
     */
    protected $token;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="user_id");
     */
    protected $userId;

    public function __construct($userId, $sid, $token)
    {
        $this->sid = $sid;
        $this->token = $token;
        $this->userId = $userId;
    }

    public function getSid()
    {
        return $this->sid;
    }

    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
