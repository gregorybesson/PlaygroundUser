<?php
namespace PlaygroundUser\Entity;

use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * An entity that represents a user log.
 *
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="user_log")
 *
 */
class UserLog implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;



    /**
     * Get the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
     **/
    protected $user;

    /**
     * @ORM\Column(name="controller_class", type="string", length=255)
     */
    protected $controllerClass;

    /**
     * @ORM\Column(name="module_name", type="string", length=255)
     */
    protected $moduleName;

    /**
     * @ORM\Column(name="route_name", type="string", length=255)
     */
    protected $routeName;

    /**
     * @ORM\Column(name="area_name", type="string", length=255)
     */
    protected $areaName;

    /**
     * @ORM\Column(type="text")
     */
    protected $uri;

    /**
     * @ORM\Column(name="action_name", type="string", length=255)
     */
    protected $actionName;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

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
     * Set the id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return the $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param field_type $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        
        return $this;
    }

    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    public function setControllerClass($controllerClass)
    {
        $this->controllerClass = $controllerClass;

        return $this;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getAreaName()
    {
        return $this->areaName;
    }

    public function setAreaName($areaName)
    {
        $this->areaName = $areaName;

        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function setActionName($actionName)
    {
        $this->actionName = $actionName;

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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
