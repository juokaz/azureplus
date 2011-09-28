<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
    
/**
 * @ORM\Entity
 * @ORM\Table(name="apps")
 */
class App
{
    const STATUS_NEW = 'new';
    const STATUS_CREATING = 'creating';
    const STATUS_CREATED = 'created';
    const STATUS_LIVE = 'live';
    const STATUS_DELETED = 'deleted';

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $api_key;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $storage_identifier;

    /**
     * @ORM\ManyToOne(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\User", inversedBy="apps")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\Configuration", cascade={"all"})
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id")
     */
    private $configuration;

    /**
     * @ORM\OneToOne(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\Source", cascade={"all"})
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id")
     */
    private $source;

    /**
     * @ORM\OneToMany(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\Database", mappedBy="app", cascade={"all"})
     */
    private $databases;

    /**
     * @ORM\OneToMany(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\Log", mappedBy="app", cascade={"all"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $log;
    
    public function __construct(User $user, $name)
    {
        $this->user = $user;
        $this->name = $name;
        $this->status = self::STATUS_NEW;
        $this->configuration = new Configuration();
        $this->source = new Source();
        $this->databases = new ArrayCollection();
        $this->log = new ArrayCollection();

        // @todo make this better
        $this->api_key = sha1($name . uniqid());
    }

    public function setName($name)
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

    public function getStorageIdentifier()
    {
        return $this->storage_identifier;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function getContainer()
    {
        return $this->getName();
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function addDatabase(Database $db)
    {
        $this->databases[] = $db;
    }

    public function getDatabases()
    {return array(new Database($this, 'aa', 'aa'));
        return $this->databases;
    }

    public function addLog(Log $log)
    {
        $this->log[] = $log;
    }

    public function addLogMessage($message, $type)
    {
        $this->addLog(new Log($this, $message, $type));
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getLastLog($count = 0)
    {
        if ($count == 0) {
            return $this->log[0];
        } else {
            return $this->log->slice(0, $count);
        }
    }
    
    public function getKey()
    {
        return $this->api_key;
    }

    public function isAutoDeployable()
    {
        return $this->getSource()->getGitRepository() != null;
    }

    public function isLive()
    {
        return $this->status == self::STATUS_LIVE;
    }
}
