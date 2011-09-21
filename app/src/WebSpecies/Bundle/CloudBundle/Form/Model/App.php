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
     * @Assert\Url(protocols={"http", "https", "git"})
     */
    private $git_repository;

    private $app_root;

    /**
     * @Assert\NotBlank()
     */
    private $location;

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

    public function setPhpVersion($php_version)
    {
        $this->php_version = $php_version;
    }

    public function getPhpVersion()
    {
        return $this->php_version;
    }

    public function setAppRoot($app_root)
    {
        $this->app_root = $app_root;
    }

    public function getAppRoot()
    {
        return $this->app_root;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }
}
