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
    private $index_file = 'index.php';

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