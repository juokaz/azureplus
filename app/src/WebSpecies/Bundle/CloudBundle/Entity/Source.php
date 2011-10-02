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
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $git_repository;

    private $repository_changed = false;

    public function setGitRepository($git_repository)
    {
        if ($this->git_repository != $git_repository) {
            $this->repository_changed = true;
        }

        $this->git_repository = $git_repository;
    }

    public function getGitRepository()
    {
        return $this->git_repository;
    }

    /**
     * Is path different from what it was initially
     *
     * @return bool
     */
    public function isGitRepositoryChanged()
    {
        return $this->repository_changed;
    }
}