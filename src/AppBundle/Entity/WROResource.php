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
     * @return WROResource
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
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $folder;

    /**
     * @var string
     */
    private $description;


    /**
     * Set filename
     *
     * @param string $filename
     * @return WROResource
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set folder
     *
     * @param string $folder
     * @return WROResource
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    
        return $this;
    }

    /**
     * Get folder
     *
     * @return string 
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return WROResource
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
}