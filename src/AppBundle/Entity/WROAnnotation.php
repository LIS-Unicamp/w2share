<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ROAnnotation
 */
class WROAnnotation
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \AppBundle\Entity\WROResource
     */
    private $resource;

    /**
     * @var \AppBundle\Entity\WRO
     */
    private $wro;


    /**
     * Set uri
     *
     * @param string $uri
     * @return ROAnnotation
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
     * Set description
     *
     * @param string $description
     * @return ROAnnotation
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ROAnnotation
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
     * Set resource
     *
     * @param \AppBundle\Entity\WROResource $resource
     * @return ROAnnotation
     */
    public function setResource(\AppBundle\Entity\WROResource $resource = null)
    {
        $this->resource = $resource;
    
        return $this;
    }

    /**
     * Get resource
     *
     * @return \AppBundle\Entity\WROResource 
     */
    public function getResource()
    {
        return $this->resource;
    }   

    /**
     * Set wro
     *
     * @param \AppBundle\Entity\WRO $wro
     * @return WROAnnotation
     */
    public function setWro(\AppBundle\Entity\WRO $wro = null)
    {
        $this->wro = $wro;
    
        return $this;
    }

    /**
     * Get wro
     *
     * @return \AppBundle\Entity\WRO 
     */
    public function getWro()
    {
        return $this->wro;
    }
}