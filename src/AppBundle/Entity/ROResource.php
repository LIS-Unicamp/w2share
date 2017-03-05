<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ROResource
 */
class ROResource
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
     * @var \AppBundle\Entity\ResearchObject
     */
    private $resources;

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
     * @param \AppBundle\Entity\ROAnnotation $annotations
     * @return ROResource
     */
    public function addAnnotation(\AppBundle\Entity\ROAnnotation $annotations)
    {
        $this->annotations[] = $annotations;
    
        return $this;
    }

    /**
     * Remove annotations
     *
     * @param \AppBundle\Entity\ROAnnotation $annotations
     */
    public function removeAnnotation(\AppBundle\Entity\ROAnnotation $annotations)
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
     * Set resources
     *
     * @param \AppBundle\Entity\ResearchObject $resources
     * @return ROResource
     */
    public function setResources(\AppBundle\Entity\ResearchObject $resources = null)
    {
        $this->resources = $resources;
    
        return $this;
    }

    /**
     * Get resources
     *
     * @return \AppBundle\Entity\ResearchObject 
     */
    public function getResources()
    {
        return $this->resources;
    }
}