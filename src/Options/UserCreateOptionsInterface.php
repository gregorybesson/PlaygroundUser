<?php

namespace PlaygroundUser\Options;

interface UserCreateOptionsInterface
{
    public function getCreateUserAutoPassword();

    public function setCreateUserAutoPassword($createUserAutoPassword);

    public function getCreateUserAutoSocial();

    public function setCreateUserAutoSocial($createUserAutoSocial);

    public function getCreateFormElements();

    public function setCreateFormElements(array $elements);

    public function getUseRecaptcha();

    public function setUseRecaptcha($use_recaptcha);
}
