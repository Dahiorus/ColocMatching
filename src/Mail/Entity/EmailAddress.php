<?php

namespace App\Mail\Entity;

class EmailAddress
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $type;


    public function __toString()
    {
        return "EmailAddress(" . $this->type . ") [" . $this->displayName . " <" . $this->address . ">]";
    }


    public function getAddress()
    {
        return $this->address;
    }


    public function setAddress(?string $address)
    {
        $this->address = $address;

        return $this;
    }


    public function getDisplayName()
    {
        return $this->displayName;
    }


    public function setDisplayName(?string $displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }

}