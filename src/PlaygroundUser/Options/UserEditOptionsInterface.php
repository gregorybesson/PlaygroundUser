<?php

namespace PlaygroundUser\Options;

interface UserEditOptionsInterface
{
    public function getEditFormElements();

    public function setEditFormElements(array $elements);

    public function getNewEmailSubjectLine();

    public function setNewEmailSubjectLine($element);
}
