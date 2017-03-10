<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ROResource
 */
class WROResource
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $annotations;

    /**
     * @var \AppBundle\Entity\WRO
     */
    private $wro;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->annotations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set uri
     *
     * @param string $uri
     * @return ROResource
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    
        return $this;
    }

    /**
     * Get uri
     *
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return ROResource
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ROResource
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Add annotations
     *
     * @param \AppBundle\Entity\WROAnnotation $annotations
     * @return WROResource
     */
    public function addAnnotation(\AppBundle\Entity\WROAnnotation $annotations)
    {
        $this->annotations[] = $annotations;
    
        return $this;
    }

    /**
     * Remove annotations
     *
     * @param \AppBundle\Entity\ROAnnotation $annotations
     */
    public function removeAnnotation(\AppBundle\Entity\WROAnnotation $annotations)
    {
        $this->annotations->removeElement($annotations);
    }

    /**
     * Get annotations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnnotations()
    {
        return $this->annotations;
    } 
    
    /**
     * Set wro
     *
     * @param \AppBundle\Entity\WRO $wro
     * @return ROAnnotation
     */
    public function setWRO(\AppBundle\Entity\WRO $wro = null)
    {
        $this->wro = $wro;
    
        return $this;
    }

    /**
     * Get wro
     *
     * @return \AppBundle\Entity\WRO 
     */
    public function getWRO()
    {
        return $this->wro;
    }
}