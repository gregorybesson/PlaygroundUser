<?php

namespace PlaygroundUser\Options;

interface ModuleOptionsInterface
{
    public function getUserMapper();

    public function setUserMapper($mapper);
}
