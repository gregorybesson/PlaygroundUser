<?php
namespace PlaygroundUser\Entity;

use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * An example entity that represents a role.
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class Role implements HierarchicalRoleInterface, InputFilterAwareInterface
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
     * @var string
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $roleId;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\Role")
     */
    protected $parent;

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
     * Get the role id.
     *
     * @return string
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set the role id.
     *
     * @param string $roleId
     *
     * @return void
     */
    public function setRoleId($roleId)
    {
        $this->roleId = (string) $roleId;

        return $this;
    }

    /**
     * Get the parent role
     *
     * @return Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the parent role.
     *
     * @param Role $role
     *
     * @return void
     */
    public function setParent(Role $parent)
    {
        $this->parent = $parent;

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
        if (isset($data['id']) && $data['id'] != null) {
            $this->id    = $data['id'];
        }

        if (isset($data['roleId']) && $data['roleId'] != null) {
            $this->roleId    = $data['roleId'];
        }
        if (isset($data['parent']) && $data['parent'] != null) {
            $this->parent    = $data['parent'];
        }
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

            $inputFilter->add($factory->createInput(array(
                'name'     => 'roleId',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                    array(
                        'name'    => 'Regex',
                        'options' => array(
                            'pattern' => '/^[a-zA-Z\'àâéèêôùûçÀÂÉÈÔÙÛÇ[:blank:]-]+$/', // Validate firstname
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'parentRoleId',
                'required' => false,
                'allowEmpty' => true,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
