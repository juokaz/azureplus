<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="configs")
 */
class Configuration
{
    /**
     * PHP Versions
     */
    const PHP_53 = 'php53';
    const PHP_52 = 'php52';

    /**
     * Possible locations
     */
    const LOCATION_NORTH_CENTRAL_US = 'North Central US';
    const LOCATION_SOUTH_CENTRAL_US = 'South Central US';
    const LOCATION_NORTH_EUROPE = 'North Europe';
    const LOCATION_WEST_EUROPE = 'West Europe';
    const LOCATION_EAST_ASIA = 'East Asia';
    const LOCATION_SOUTHEAST_ASIA = 'Southeast Asia';
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $production = false;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $php_version = self::PHP_53;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $app_root = '';

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $location = self::LOCATION_WEST_EUROPE;

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

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setProduction($production)
    {
        $this->production = $production;
    }

    public function getProduction()
    {
        return $this->production;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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
