<?php

namespace PlaygroundUser\Mapper;

use ZfcUser\Mapper\UserInterface as ZfcUserMapperInterface;

interface UserInterface extends ZfcUserMapperInterface
{
    public function findByEmail($email);

    public function findByUsername($username);

    public function findById($id);

    public function insert($user);

    public function update($user);
}
