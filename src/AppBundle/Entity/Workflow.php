<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Workflow
 */
class Workflow
{
    /**
     * @var integer
     */
    private $id;    

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $description;
    

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }    

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Workflow
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
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

    public function preUpload()
    {
        if (null !== $this->getWorkflowFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->workflow_path = $filename.'.'.$this->getWorkflowFile()->getClientOriginalExtension();
        }
        
        if (null !== $this->getProvenanceFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->provenance_path = $filename.'.'.$this->getProvenanceFile()->getClientOriginalExtension();
        }
    }

    public function upload()
    {
        if (null === $this->getWorkflowFile() && null === $this->getProvenanceFile()) {
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
}
