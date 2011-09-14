<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sources")
 */
class Source
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $git_repository;

    public function setGitRepository($git_repository)
    {
        $this->git_repository = $git_repository;
    }

    public function getGitRepository()
    {
        return $this->git_repository;
    }
}