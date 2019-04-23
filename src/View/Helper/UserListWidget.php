<?php

namespace PlaygroundUser\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

class UserListWidget extends AbstractHelper
{
    /**
     * @var UserService
     */
    protected $userService;

    public function __construct(\PlaygroundUser\Service\User $userService) 
    {
        return $this->userService = $userService;
    }

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($roleId = 1, $order = null, $search = null, $filterField = null, $filterValue = null)
    {
        $query = $this->getUserService()->getQueryUsersByRole($roleId, $order, $search, $filterField, $filterValue);
        $users = $query->getResult();

        return $users;
    }

    /**
     * Get userService.
     *
     * @return UserService
     */
    public function getUserService()
    {
        return $this->userService;
    }
}