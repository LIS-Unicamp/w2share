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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $outputs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inputs;

    /**
     * @var \AppBundle\Entity\WorkflowRun
     */
    private $workflow_run;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->outputs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inputs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add outputs
     *
     * @param \AppBundle\Entity\OutputRun $outputs
     * @return ProcessRun
     */
    public function addOutput(\AppBundle\Entity\OutputRun $outputs)
    {
        $this->outputs[] = $outputs;
    
        return $this;
    }

    /**
     * Remove outputs
     *
     * @param \AppBundle\Entity\OutputRun $outputs
     */
    public function removeOutput(\AppBundle\Entity\OutputRun $outputs)
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
     * Add inputs
     *
     * @param \AppBundle\Entity\InputRun $inputs
     * @return ProcessRun
     */
    public function addInput(\AppBundle\Entity\InputRun $inputs)
    {
        $this->inputs[] = $inputs;
    
        return $this;
    }

    /**
     * Remove inputs
     *
     * @param \AppBundle\Entity\InputRun $inputs
     */
    public function removeInput(\AppBundle\Entity\InputRun $inputs)
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
}