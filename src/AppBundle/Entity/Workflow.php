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
        if ($this->title)
        {
            return $this->title;
        }
        return $this->label;
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
            $this->workflow_path = sha1(uniqid(mt_rand(), true));
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
            $this->provenance_path = sha1(uniqid(mt_rand(), true));
        }
    }
    
    public function createWfdescFile()
    {
        $command = "java -jar ". __DIR__ . "/../../../src/AppBundle/Utils/scufl2-wfdesc-0.3.7-standalone.jar ".$this->getWorkflowAbsolutePath();                              
        exec($command);  
    }
    
    public function fileNames()
    {
        $this->provenance_path = 'workflow.prov.ttl';
        $this->workflow_path = 'workflow.t2flow';
    }

    public function preUpload()
    {
        if (null !== $this->getWorkflowFile()) 
        {
            $this->workflow_path = 'workflow.t2flow';
        }
        
        if (null !== $this->getProvenanceFile()) 
        {
            $this->provenance_path = 'workflow.prov.ttl';
        }                
    }

    public function upload()
    {
        if (null === $this->getWorkflowFile() 
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
        
    }

    public function removeUpload()
    {
        $this->fileNames();
        $workflow_file = $this->getWorkflowAbsolutePath();
        if ($workflow_file) {
            unlink($workflow_file);
        }
        
        $provenance_file = $this->getProvenanceAbsolutePath();
        if ($provenance_file) {
            unlink($provenance_file);
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
        if ($this->provenance_file)
        {
            return $this->provenance_file;
        }
        else if (file_exists($this->getProvenanceAbsolutePath()))
        {
            return file_get_contents($this->getProvenanceAbsolutePath());
        }
        return null;
    }
    
    public function getProvenanceAbsolutePath()
    {
        return $this->getUploadRootDir().'/workflowrun.prov.ttl';
    }
    
    public function getWfdescAbsolutePath()
    {
        return $this->getUploadRootDir().'/workflow.wfdesc.ttl';
    }
    
    public function getWorkflowAbsolutePath()
    {
        return $this->getUploadRootDir().'/workflow.t2flow';
    }        

    public function getWebPath()
    {
        return $this->getUploadDir()."/".$this->getHash();
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir()."/".$this->getHash();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents/w2share';
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
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $processes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inputs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $outputs;


    /**
     * Add processes
     *
     * @param \AppBundle\Entity\Process $processes
     * @return Workflow
     */
    public function addProcess(\AppBundle\Entity\Process $processes)
    {
        $this->processes[] = $processes;
    
        return $this;
    }

    /**
     * Remove processes
     *
     * @param \AppBundle\Entity\Process $processes
     */
    public function removeProcess(\AppBundle\Entity\Process $processes)
    {
        $this->processes->removeElement($processes);
    }

    /**
     * Get processes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * Add inputs
     *
     * @param \AppBundle\Entity\Input $inputs
     * @return Workflow
     */
    public function addInput(\AppBundle\Entity\Input $inputs)
    {
        $this->inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove inputs
     *
     * @param \AppBundle\Entity\Input $inputs
     */
    public function removeInput(\AppBundle\Entity\Input $inputs)
    {
        $this->inputs->removeElement($inputs);
    }

    /**
     * Get inputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Add outputs
     *
     * @param \AppBundle\Entity\Output $outputs
     * @return Workflow
     */
    public function addOutput(\AppBundle\Entity\Output $outputs)
    {
        $this->outputs[] = $outputs;
    
        return $this;
    }

    /**
     * Remove outputs
     *
     * @param \AppBundle\Entity\Output $outputs
     */
    public function removeOutput(\AppBundle\Entity\Output $outputs)
    {
        $this->outputs->removeElement($outputs);
    }

    /**
     * Get outputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOutputs()
    {
        return $this->outputs;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $workflow_runs;


    /**
     * Add workflow_runs
     *
     * @param \AppBundle\Entity\WorkflowRun $workflowRuns
     * @return Workflow
     */
    public function addWorkflowRun(\AppBundle\Entity\WorkflowRun $workflowRun)
    {
        $this->workflow_runs[] = $workflowRun;
    
        return $this;
    }
    
    /**
     * set workflow_runs
     *
     * @param array $workflowRuns
     * @return Workflow
     */
    public function setWorkflowRuns(array $workflowRuns)
    {
        $this->workflow_runs = new \Doctrine\Common\Collections\ArrayCollection($workflowRuns);
    
        return $this;
    }

    /**
     * Remove workflow_runs
     *
     * @param \AppBundle\Entity\WorkflowRun $workflowRuns
     */
    public function removeWorkflowRun(\AppBundle\Entity\WorkflowRun $workflowRun)
    {
        $this->workflow_runs->removeElement($workflowRun);
    }

    /**
     * Get workflow_runs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWorkflowRuns()
    {
        return $this->workflow_runs;
    }

    /**
     * Add processes
     *
     * @param \AppBundle\Entity\Process $processes
     * @return Workflow
     */
    public function addProcesse(\AppBundle\Entity\Process $processes)
    {
        $this->processes[] = $processes;
    
        return $this;
    }

    /**
     * Remove processes
     *
     * @param \AppBundle\Entity\Process $processes
     */
    public function removeProcesse(\AppBundle\Entity\Process $processes)
    {
        $this->processes->removeElement($processes);
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $quality_annotation;


    /**
     * Add quality_annotation
     *
     * @param \AppBundle\Entity\QualityAnnotation $qualityAnnotation
     * @return Workflow
     */
    public function addQualityAnnotation(\AppBundle\Entity\QualityAnnotation $qualityAnnotation)
    {
        $this->quality_annotation[] = $qualityAnnotation;
    
        return $this;
    }

    /**
     * Remove quality_annotation
     *
     * @param \AppBundle\Entity\QualityAnnotation $qualityAnnotation
     */
    public function removeQualityAnnotation(\AppBundle\Entity\QualityAnnotation $qualityAnnotation)
    {
        $this->quality_annotation->removeElement($qualityAnnotation);
    }

    /**
     * Get quality_annotation
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQualityAnnotation()
    {
        return $this->quality_annotation;
    }
}