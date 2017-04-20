<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @var UploadedFile
     */
    private $file;

    /**
     * @var File
     */
    private $wro_temp;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->annotations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {     
        $this->file = $file;        
    }  
    
    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }  
    
    public function upload()
    {
        if (null === $this->getFile()) 
        {
            return;
        }        
        else
        {
            $this->filename = $this->getFile()->getClientOriginalName();
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getFile()->move($this->getUploadRootDir(), basename($this->getAbsolutePath()));

            // check if we have an old image
            if (isset($this->wro_temp) && $this->wro_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->wro_temp);
                // clear the temp image path
                $this->wro_temp = null;
            }
            $this->file = null;
        }                
        
    }

    public function removeUpload()
    {
        @unlink($this->getAbsolutePath());
    }
    
    public function getAbsolutePath()
    {
        return $this->getUploadRootDir().'/'.$this->getFilename();
    }
    
    public function getFileContent()
    {
        return file_get_contents($this->getAbsolutePath());
    }        
    
    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->getFilename();
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        if ($this->getWro())
        {
            return 'uploads/documents/w2share/'.$this->getWro()->getHash();
        }
        else {
            return 'uploads/documents/wro';
        }
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
        if (null == $this->filename)
        {
            return basename($this->getUri());
        }
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
    /**
     * @var string
     */
    private $title;


    /**
     * Set title
     *
     * @param string $title
     * @return WROResource
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
}