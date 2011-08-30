<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

class App
{
    private $name;

    private $storage_identifier;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setStorageIdentifier($identifier)
    {
        $this->storage_identifier = $identifier;
    }
    
    public function getContainer()
    {
        return $this->getName();
    }
}
