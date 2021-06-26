<?php
namespace PlaygroundUser\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="user_team")
 */
class Team implements InputFilterAwareInterface
{

    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    protected $identifier;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=true)
     */
    protected $logo;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
     **/
    protected $host;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="PlaygroundUser\Entity\User", inversedBy="teams", cascade={"persist"})
     * @ORM\JoinTable(name="user_team_user",
     *      joinColumns={@ORM\JoinColumn(name="team_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")}
     * )
     */
    protected $users;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set host.
     *
     * @param string $host
     *
     * @return void
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param Ambigous <User, \Doctrine\Common\Collections\ArrayCollection> $users
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Add users to the team.
     *
     * @param ArrayCollection $users
     *
     * @return void
     */
    public function addUsers(ArrayCollection $users)
    {
        foreach ($users as $user) {
            $user->addTeam($this);
            $this->users->add($user);
        }
    }

    /**
     * Remove users from the team.
     *
     * @param ArrayCollection $users
     *
     * @return void
     */
    public function removeUsers(ArrayCollection $users)
    {
        foreach ($users as $user) {
            $user->removeTeam($this);
            $this->users->removeElement($user);
        }
    }

    /**
     * Add a single user to the team.
     *
     * @param User $user
     *
     * @return void
     */
    public function addUser($user)
    {
        $this->users[] = $user;
    }

    /**
     * Remove a single user from the team.
     *
     * @param User $user
     *
     * @return void
     */
    public function removeUser($user)
    {
        $this->users->removeElement($user);
    }

    /**
     * @return the string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return the string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param unknown_type $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param unknown_type $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param unknown_type $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $obj_vars = get_object_vars($this);

        return $obj_vars;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                    'name'       => 'id',
                    'required'   => false,
                    'filters' => array(
                        array('name'    => 'Int'),
                    ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
