<?php
namespace PlaygroundUser\Entity;

interface UserInterface
{
    public function createChrono();
    public function updateChrono();
    public function addRole($role);
    public function getFirstname();
    public function setFirstname($firstname);
    public function getLastname();
    public function setLastname($lastname);
    public function getTitle();
    public function setTitle($title);
    public function getGender();
    public function setGender($gender);
    public function getDob();
    public function setDob($dob);
    public function getAvatar();
    public function setAvatar($avatar);
    public function getAddress();
    public function setAddress($address);
    public function getAddress2();
    public function setAddress2($address2);
    public function getPostalCode();
    public function setPostalCode($postal_code);
    public function getCity();
    public function setCity($city);
    public function setRoles($roles);
    public function getTelephone();
    public function setTelephone($telephone);
    public function getMobile();
    public function setMobile($mobile);
    public function getOptin ();
    public function setOptin ($optin);
    public function getOptinPartner ();
    public function setOptinPartner ($optinPartner);
    public function getCreatedAt();
    public function setCreatedAt($created_at);
    public function getUpdatedAt();
    public function setUpdatedAt($updated_at);
}
