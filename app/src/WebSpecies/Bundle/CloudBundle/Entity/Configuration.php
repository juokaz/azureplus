<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="configs")
 */
class Configuration
{
    const PHP_53 = 'php53';
    const PHP_52 = 'php52';
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $php_version = self::PHP_53;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $app_root = '';

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
        $this->app_root = rtrim(ltrim($app_root, '/'), '/');
    }

    public function getAppRoot()
    {
        return $this->app_root;
    }

    public function getRouter()
    {
        $split = strrpos($this->app_root, '/');
        
        if (!$split && strpos($this->app_root, '.') !== false) {
            return $this->app_root;
        } elseif (!$split) {
            return '';
        }

        return substr($this->app_root, $split + 1, strlen($this->app_root) + $split);
    }

    public function getPublicFolder()
    {
        $split = strrpos($this->app_root, '/');

        if (!$split && strpos($this->app_root, '.') === false) {
            return $this->app_root;
        } elseif (!$split) {
            return '';
        }

        return substr($this->app_root, 0, $split);
    }
}