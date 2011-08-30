<?php

namespace WebSpecies\Bundle\CloudBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class App
{
    /**
     * @Assert\NotBlank()
     */
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
}
