<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Workflow
 */
class Workflow
{    
    /**
     * @var string
     */
    private $creator;
    
    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $description;
       

    public function __construct() {
        $this->hash = sha1(uniqid(mt_rand(), true));
    }
    
    /**
     * @var string
     */
    private $label;
    
    /**
     * Set label
     *
     * @param string $label
     *
     * @return Workflow
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }    
    
    /**
     * Set creator
     *
     * @param string $creator
     *
     * @return Workflow
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return Workflow
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
    
    /**
     * Set description
     *
     * @param string $description
     *
     * @return Workflow
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
     *
     * @return Workflow
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
            
    private $workflow_file;

    private $workflow_temp;
    
    private $provenance_file;

    private $provenance_temp;
    
    private $wfdesc_file;

    private $wfdesc_temp;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setWorkflowFile(UploadedFile $file = null)
    {     
        $this->workflow_file = $file;
        // check if we have an old image path
        if (isset($this->workflow_path)) {
            // store the old name to delete after the update
            $this->workflow_temp = $this->workflow_path;
            $this->workflow_path = null;
        } else {
            $this->workflow_path = 'initial';
        }
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setProvenanceFile(UploadedFile $file = null)
    {
        $this->provenance_file = $file;
        // check if we have an old image path
        if (isset($this->provenance_path)) {
            // store the old name to delete after the update
            $this->provenance_temp = $this->provenance_path;
            $this->provenance_path = null;
        } else {
            $this->provenance_path = 'initial';
        }
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setWfdescFile(UploadedFile $file = null)
    {
        $this->wfdesc_file = $file;
        // check if we have an old image path
        if (isset($this->wfdesc_path)) {
            // store the old name to delete after the update
            $this->wfdesc_temp = $this->wfdesc_path;
            $this->wfdesc_path = null;
        } else {
            $this->wfdesc_path = 'initial';
        }
    }

    public function preUpload()
    {
        $filename = $this->getHash();
        if (null !== $this->getWorkflowFile()) 
        {
            $this->workflow_path = $filename.'.'.$this->getWorkflowFile()->getClientOriginalExtension();
        }
        
        if (null !== $this->getProvenanceFile()) 
        {
            $this->provenance_path = $filename.'.'.$this->getProvenanceFile()->getClientOriginalExtension();
        }
        
        if (null !== $this->getWfdescFile()) 
        {
            $this->wfdesc_path = $filename.'.'.$this->getWfdescFile()->getClientOriginalExtension();
        }
    }

    public function upload()
    {
        if (null === $this->getWorkflowFile() 
                && null === $this->getWfdescFile() 
                && null === $this->getProvenanceFile()) {
            return;
        }
        
        if (null !== $this->getWorkflowFile())
        {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getWorkflowFile()->move($this->getUploadRootDir(), $this->workflow_path);

            // check if we have an old image
            if (isset($this->workflow_temp) && $this->workflow_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->workflow_temp);
                // clear the temp image path
                $this->workflow_temp = null;
            }
            $this->workflow_file = null;
        }
        
        if (null !== $this->getProvenanceFile())
        {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getProvenanceFile()->move($this->getUploadRootDir(), $this->provenance_path);

            // check if we have an old image
            if (isset($this->provenance_temp) && $this->provenance_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->provenance_temp);
                // clear the temp image path
                $this->provenance_temp = null;
            }
            $this->provenance_file = null;
        }
        
        if (null !== $this->getWfdescFile())
        {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getWfdescFile()->move($this->getUploadRootDir(), $this->wfdesc_path);

            // check if we have an old image
            if (isset($this->wfdesc_temp) && $this->wfdesc_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->wfdesc_temp);
                // clear the temp image path
                $this->wfdesc_temp = null;
            }
            $this->wfdesc_file = null;
        }
        
    }

    public function removeUpload()
    {
        $workflow_file = $this->getWorkflowAbsolutePath();
        if ($workflow_file) {
            @unlink($workflow_file);
        }
        
        $provenance_file = $this->getProvenanceAbsolutePath();
        if ($provenance_file) {
            @unlink($provenance_file);
        }
        
        $wfdesc_file = $this->getWfdescAbsolutePath();
        if ($wfdesc_file) {
            @unlink($wfdesc_file);
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getWorkflowFile()
    {
        return $this->workflow_file;
    }
    
    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getWfdescFile()
    {
        return $this->wfdesc_file;
    }
    
    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getProvenanceFile()
    {
        return $this->provenance_file;
    }
    
    public function getProvenanceAbsolutePath()
    {
        return null === $this->provenance_path && $this->provenance_path != ''
            ? null
            : $this->getUploadRootDir().'/'.$this->provenance_path;
    }
    
    public function getWorkflowAbsolutePath()
    {
        return null === $this->workflow_path && $this->workflow_path != ''
            ? null
            : $this->getUploadRootDir().'/'.$this->workflow_path;
    }
    
    public function getWfdescAbsolutePath()
    {
        return null === $this->wfdesc_path && $this->wfdesc_path != ''
            ? null
            : $this->getUploadRootDir().'/'.$this->wfdesc_path;
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
        return 'uploads/documents';
    }
        
    /**
     * @var string
     */
    private $uri;


    /**
     * Set uri
     *
     * @param string $uri
     *
     * @return Workflow
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
     * @var string
     */
    private $workflow_path;

    /**
     * @var string
     */
    private $provenance_path;


    /**
     * Set workflowPath
     *
     * @param string $workflowPath
     *
     * @return Workflow
     */
    public function setWorkflowPath($workflowPath)
    {
        $this->workflow_path = $workflowPath;

        return $this;
    }

    /**
     * Get workflowPath
     *
     * @return string
     */
    public function getWorkflowPath()
    {
        return $this->workflow_path;
    }

    /**
     * Set provenancePath
     *
     * @param string $provenancePath
     *
     * @return Workflow
     */
    public function setProvenancePath($provenancePath)
    {
        $this->provenance_path = $provenancePath;

        return $this;
    }

    /**
     * Get provenancePath
     *
     * @return string
     */
    public function getProvenancePath()
    {
        return $this->provenance_path;
    }
    /**
     * @var string
     */
    private $wfdesc_path;


    /**
     * Set wfdescPath
     *
     * @param string $wfdescPath
     *
     * @return Workflow
     */
    public function setWfdescPath($wfdescPath)
    {
        $this->wfdesc_path = $wfdescPath;

        return $this;
    }

    /**
     * Get wfdescPath
     *
     * @return string
     */
    public function getWfdescPath()
    {
        return $this->wfdesc_path;
    }
    
    public function __toString() {
        return $this->title ? $this->title : $this->label;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $workflow;


    /**
     * Add workflow
     *
     * @param \AppBundle\Entity\Process $workflow
     * @return Workflow
     */
    public function addWorkflow(\AppBundle\Entity\Process $workflow)
    {
        $this->workflow[] = $workflow;
    
        return $this;
    }

    /**
     * Remove workflow
     *
     * @param \AppBundle\Entity\Process $workflow
     */
    public function removeWorkflow(\AppBundle\Entity\Process $workflow)
    {
        $this->workflow->removeElement($workflow);
    }

    /**
     * Get workflow
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }
}