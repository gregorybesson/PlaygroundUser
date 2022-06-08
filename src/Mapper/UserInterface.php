<?php

namespace PlaygroundUser\Mapper;

use LmcUser\Mapper\UserInterface as LmcUserMapperInterface;

interface UserInterface extends LmcUserMapperInterface
{
    public function findByEmail($email);

    public function findByUsername($username);

    public function findById($id);

    public function insert($user);

    public function update($user);
}
