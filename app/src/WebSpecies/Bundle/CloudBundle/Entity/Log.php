<?php

namespace WebSpecies\Bundle\CloudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="logs")
 * @ORM\HasLifecycleCallbacks
 */
class Log
{
    const CAT_GENERAL = 'general';
    const CAT_DEPLOY = 'deploy';
    const CAT_SETUP = 'setup';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", length=100)
     * @var \DateTime
     */
    private $time;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="WebSpecies\Bundle\CloudBundle\Entity\App", inversedBy="databases")
     * @ORM\JoinColumn(name="app_id", referencedColumnName="name")
     */
    private $app;

    public function __construct(App $app, $message, $category)
    {
        $this->app = $app;
        $this->message = $message;
        $this->category = $category;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getFormattedTime()
    {
        return $this->time->format('Y-m-d H:i:s');
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    /** @ORM\PrePersist */
    public function doStuffOnPrePersist()
    {
        $this->time = new \DateTime();
    }
}