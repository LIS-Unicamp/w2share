<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ROAnnotation
 */
class ROAnnotation
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
     * @var \AppBundle\Entity\ROResource
     */
    private $resource;

    /**
     * @var \AppBundle\Entity\ResearchObject
     */
    private $research_object;


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
     * @param \AppBundle\Entity\ROResource $resource
     * @return ROAnnotation
     */
    public function setResource(\AppBundle\Entity\ROResource $resource = null)
    {
        $this->resource = $resource;
    
        return $this;
    }

    /**
     * Get resource
     *
     * @return \AppBundle\Entity\ROResource 
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set research_object
     *
     * @param \AppBundle\Entity\ResearchObject $researchObject
     * @return ROAnnotation
     */
    public function setResearchObject(\AppBundle\Entity\ResearchObject $researchObject = null)
    {
        $this->research_object = $researchObject;
    
        return $this;
    }

    /**
     * Get research_object
     *
     * @return \AppBundle\Entity\ResearchObject 
     */
    public function getResearchObject()
    {
        return $this->research_object;
    }
}