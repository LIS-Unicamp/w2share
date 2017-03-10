<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ResearchObject
 */
class WRO
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
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $resources;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $annotations;

    /**
     * @var \AppBundle\Entity\Person
     */
    private $creator;    
    
    /**
     * @var UploadedFile
     */
    private $wro_file;

    /**
     * @var File
     */
    private $wro_temp;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->annotations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->hash = sha1(uniqid(mt_rand(), true));
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setWROFile(UploadedFile $file = null)
    {     
        $this->wro_file = $file;        
    }    
    
    public function upload()
    {
        if (null === $this->getWROFile()) 
        {
            return;
        }        
        else
        {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getWROFile()->move($this->getUploadRootDir(), $this->wro_path);

            // check if we have an old image
            if (isset($this->wro_temp) && $this->wro_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->wro_temp);
                // clear the temp image path
                $this->wro_temp = null;
            }
            $this->wro_file = null;
        }                
        
    }

    public function removeUpload()
    {
        \AppBundle\Utils\Utils::unlinkr($this->getUploadRootDir());               
    }
    
    public function getWROAbsolutePath()
    {
        return $this->getUploadRootDir().'/wro-bundle.zip';
    }
    
    public function getWebPath()
    {
        return $this->getUploadDir().'/wro-bundle.zip';
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir().'/'.$this->getHash();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents/wro';
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getWROFile()
    {
        return $this->wro_file;
    }        
    
    /**
     * Set uri
     *
     * @param string $uri
     * @return ResearchObject
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
     * @return ResearchObject
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
     * Set title
     *
     * @param string $title
     * @return ResearchObject
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ResearchObject
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
     * Add resources
     *
     * @param \AppBundle\Entity\WROResource $resources
     * @return ResearchObject
     */
    public function addResource(\AppBundle\Entity\WROResource $resources)
    {
        $this->resources[] = $resources;
    
        return $this;
    }

    /**
     * Remove resources
     *
     * @param \AppBundle\Entity\WROResource $resources
     */
    public function removeResource(\AppBundle\Entity\WROResource $resources)
    {
        $this->resources->removeElement($resources);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add annotations
     *
     * @param \AppBundle\Entity\WROAnnotation $annotations
     * @return ResearchObject
     */
    public function addAnnotation(\AppBundle\Entity\WROAnnotation $annotations)
    {
        $this->annotations[] = $annotations;
    
        return $this;
    }

    /**
     * Remove annotations
     *
     * @param \AppBundle\Entity\WROAnnotation $annotations
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
     * Set creator
     *
     * @param \AppBundle\Entity\Person $creator
     * @return ResearchObject
     */
    public function setCreator(\AppBundle\Entity\Person $creator = null)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\Person 
     */
    public function getCreator()
    {
        return $this->creator;
    }
    
    /**
     * @var string
     */
    private $hash;


    /**
     * Set hash
     *
     * @param string $hash
     * @return ResearchObject
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    
        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }
}