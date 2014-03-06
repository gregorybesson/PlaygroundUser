<?php
namespace PlaygroundUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="user_provider") */
class UserProvider
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
     **/
    protected $user;

    /** @ORM\Column(type="string",length=50,name="provider_id") */
    protected $providerId;

    /** @ORM\Column(type="string") */
    protected $provider;

     /**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  integer      $id
     * @return UserProvider
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return the $userId
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  integer      $userId
     * @return UserProvider
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return the $providerId
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @param  integer      $providerId
     * @return UserProvider
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * @return the $provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param  string       $provider
     * @return UserProvider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
