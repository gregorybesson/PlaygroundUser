<?php

namespace PlaygroundUser\Mapper;

use Zend\Hydrator\ClassMethods;

class RememberMeHydrator extends ClassMethods
{
    public function extract($object)
    {
        $data = parent::extract($object);

        return $data;
    }

    public function hydrate(array $data, $object)
    {
        return parent::hydrate($data, $object);
    }
}
