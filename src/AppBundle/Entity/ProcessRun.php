<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessRun
 */
class ProcessRun
{
    /**
     * @var string
     */
    private $uri;   

    /**
     * @var \AppBundle\Entity\WorkflowRun
     */
    private $workflow_run;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->outputs_run = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inputs_run = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set uri
     *
     * @param string $uri
     * @return ProcessRun
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
     * Set workflow_run
     *
     * @param \AppBundle\Entity\WorkflowRun $workflowRun
     * @return ProcessRun
     */
    public function setWorkflowRun(\AppBundle\Entity\WorkflowRun $workflowRun = null)
    {
        $this->workflow_run = $workflowRun;
    
        return $this;
    }

    /**
     * Get workflow_run
     *
     * @return \AppBundle\Entity\WorkflowRun 
     */
    public function getWorkflowRun()
    {
        return $this->workflow_run;
    }        

    /**
     * @var string
     */
    private $label;  

    /**
     * Set label
     *
     * @param string $label
     * @return ProcessRun
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
     * @var \DateTime
     */
    private $started_at_time;

    /**
     * @var \DateTime
     */
    private $ended_at_time;


    /**
     * Set started_at_time
     *
     * @param \DateTime $startedAtTime
     * @return ProcessRun
     */
    public function setStartedAtTime($startedAtTime)
    {
        $this->started_at_time = $startedAtTime;
    
        return $this;
    }

    /**
     * Get started_at_time
     *
     * @return \DateTime 
     */
    public function getStartedAtTime()
    {
        return $this->started_at_time;
    }

    /**
     * Set ended_at_time
     *
     * @param \DateTime $endedAtTime
     * @return ProcessRun
     */
    public function setEndedAtTime($endedAtTime)
    {
        $this->ended_at_time = $endedAtTime;
    
        return $this;
    }

    /**
     * Get ended_at_time
     *
     * @return \DateTime 
     */
    public function getEndedAtTime()
    {
        return $this->ended_at_time;
    }
    /**
     * @var \AppBundle\Entity\Process
     */
    private $process;


    /**
     * Set process
     *
     * @param \AppBundle\Entity\Process $process
     * @return ProcessRun
     */
    public function setProcess(\AppBundle\Entity\Process $process = null)
    {
        $this->process = $process;
    
        return $this;
    }

    /**
     * Get process
     *
     * @return \AppBundle\Entity\Process 
     */
    public function getProcess()
    {
        return $this->process;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $outputs_run;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inputs_run;


    /**
     * Add outputs_run
     *
     * @param \AppBundle\Entity\OutputRun $outputsRun
     * @return ProcessRun
     */
    public function addOutputsRun(\AppBundle\Entity\OutputRun $outputsRun)
    {
        $this->outputs_run[] = $outputsRun;
    
        return $this;
    }

    /**
     * Remove outputs_run
     *
     * @param \AppBundle\Entity\OutputRun $outputsRun
     */
    public function removeOutputsRun(\AppBundle\Entity\OutputRun $outputsRun)
    {
        $this->outputs_run->removeElement($outputsRun);
    }

    /**
     * Get outputs_run
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOutputsRun()
    {
        return $this->outputs_run;
    }

    /**
     * Add inputs_run
     *
     * @param \AppBundle\Entity\InputRun $inputsRun
     * @return ProcessRun
     */
    public function addInputsRun(\AppBundle\Entity\InputRun $inputsRun)
    {
        $this->inputs_run[] = $inputsRun;
    
        return $this;
    }

    /**
     * Remove inputs_run
     *
     * @param \AppBundle\Entity\InputRun $inputsRun
     */
    public function removeInputsRun(\AppBundle\Entity\InputRun $inputsRun)
    {
        $this->inputs_run->removeElement($inputsRun);
    }

    /**
     * Get inputs_run
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputsRun()
    {
        return $this->inputs_run;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $quality_annotation;


    /**
     * Add quality_annotation
     *
     * @param \AppBundle\Entity\QualityAnnotation $qualityAnnotation
     * @return ProcessRun
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