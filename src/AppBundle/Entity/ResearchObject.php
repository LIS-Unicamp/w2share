<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ResearchObject
 */
class ResearchObject
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
     * @var string
     */
    private $ro_path;
    
    /**
     * @var UploadedFile
     */
    private $ro_file;

    /**
     * @var File
     */
    private $ro_temp;

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
    public function setROFile(UploadedFile $file = null)
    {     
        $this->ro_file = $file;
        // check if we have an old image path
        if (isset($this->ro_path)) {
            // store the old name to delete after the update
            $this->ro_temp = $this->ro_path;
            $this->ro_path = null;
        } else {
            $this->ro_path = sha1(uniqid(mt_rand(), true));
        }
    }
    
    public function preUpload()
    {
        $filename = $this->getHash();
        if (null !== $this->getROFile()) 
        {
            $this->ro_path = $filename.'.'.$this->getROFile()->getClientOriginalExtension();
        }
    }
    
    public function upload()
    {
        if (null === $this->getROFile() 
                && null === $this->getWfdescFile() 
                && null === $this->getProvenanceFile()) {
            return;
        }
        
        if (null !== $this->getROFile())
        {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getROFile()->move($this->getUploadRootDir(), $this->ro_path);

            // check if we have an old image
            if (isset($this->ro_temp) && $this->ro_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->ro_temp);
                // clear the temp image path
                $this->ro_temp = null;
            }
            $this->ro_file = null;
        }                
        
    }

    public function removeUpload()
    {
        $this->fileNames();
        $ro_file = $this->getROAbsolutePath();
        if ($ro_file) {
            unlink($ro_file);
        }        
    }
    
    public function getROAbsolutePath()
    {
        return null === $this->ro_path && $this->ro_path != ''
            ? null
            : $this->getUploadRootDir().'/'.$this->ro_path;
    }
    
    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents/ro';
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getROFile()
    {
        return $this->ro_file;
    }
    
    /**
     * Set roPath
     *
     * @param string $roPath
     *
     * @return RO
     */
    public function setROPath($roPath)
    {
        $this->ro_path = $roPath;

        return $this;
    }

    /**
     * Get roPath
     *
     * @return string
     */
    public function getROPath()
    {
        return $this->ro_path;
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
     * @param \AppBundle\Entity\ROResource $resources
     * @return ResearchObject
     */
    public function addResource(\AppBundle\Entity\ROResource $resources)
    {
        $this->resources[] = $resources;
    
        return $this;
    }

    /**
     * Remove resources
     *
     * @param \AppBundle\Entity\ROResource $resources
     */
    public function removeResource(\AppBundle\Entity\ROResource $resources)
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
     * @param \AppBundle\Entity\ROAnnotation $annotations
     * @return ResearchObject
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