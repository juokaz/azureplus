<?php

namespace WebSpecies\Bundle\CloudBundle\Document;

class App
{
    private $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getContainer()
    {
        return $this->getName();
    }
}
