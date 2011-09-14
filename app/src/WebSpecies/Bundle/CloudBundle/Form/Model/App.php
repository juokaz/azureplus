<?php

namespace WebSpecies\Bundle\CloudBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class App
{
    /**
     * @Assert\NotBlank()
     * @Assert\Regex("/^\w+$/")
     */
    private $name;
    
    /**
     * @Assert\NotBlank()
     */
    private $php_version;

    /**
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"http", "https", "git"})
     */
    private $git_repository;

    /**
     * @Assert\NotBlank()
     */
    private $index_file;

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setGitRepository($git_repository)
    {
        $this->git_repository = $git_repository;
    }

    public function getGitRepository()
    {
        return $this->git_repository;
    }

    public function setIndexFile($index_file)
    {
        $this->index_file = $index_file;
    }

    public function getIndexFile()
    {
        return $this->index_file;
    }

    public function setPhpVersion($php_version)
    {
        $this->php_version = $php_version;
    }

    public function getPhpVersion()
    {
        return $this->php_version;
    }
}
