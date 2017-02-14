<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkflowRun
 */
class WorkflowRun
{
    /**
     * @var string
     */
    private $uri;


    /**
     * Set uri
     *
     * @param string $uri
     * @return WorkflowRun
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
     * @var \AppBundle\Entity\Workflow
     */
    private $workflow;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inputs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->outputs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add processes
     *
     * @param \AppBundle\Entity\ProcessRun $processes
     * @return WorkflowRun
     */
    public function addProcess(\AppBundle\Entity\ProcessRun $process)
    {
        $this->processes[] = $process;
    
        return $this;
    }
    
    /**
     * Set processes
     *
     * @param \Doctrine\Common\Collections\Collection 
     * @return WorkflowRun
     */
    public function setProcesses(array $processes)
    {        
        $this->processes = new \Doctrine\Common\Collections\ArrayCollection($processes);
    
        return $this;
    }

    /**
     * Remove processes
     *
     * @param \AppBundle\Entity\ProcessRun $processes
     */
    public function removeProcess(\AppBundle\Entity\ProcessRun $process)
    {
        $this->processes->removeElement($process);
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
     * @param \AppBundle\Entity\InputRun $inputs
     * @return WorkflowRun
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
     * Set inputs
     *
     * @param \Doctrine\Common\Collections\Collection 
     * @return WorkflowRun
     */
    public function setInputs(array $inputs)
    {        
        $this->inputs = new \Doctrine\Common\Collections\ArrayCollection($inputs);
    
        return $this;
    }

    /**
     * Add outputs
     *
     * @param \AppBundle\Entity\OutputRun $outputs
     * @return WorkflowRun
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
     * Set outputs
     *
     * @param \Doctrine\Common\Collections\Collection 
     * @return WorkflowRun
     */
    public function setOutputs(array $outputs)
    {        
        $this->outputs = new \Doctrine\Common\Collections\ArrayCollection($outputs);
    
        return $this;
    }

    /**
     * Set workflow
     *
     * @param \AppBundle\Entity\Workflow $workflow
     * @return WorkflowRun
     */
    public function setWorkflow(\AppBundle\Entity\Workflow $workflow = null)
    {
        $this->workflow = $workflow;
    
        return $this;
    }

    /**
     * Get workflow
     *
     * @return \AppBundle\Entity\Workflow 
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }
    /**
     * @var string
     */
    private $label;

    /**
     * @var \DateTime
     */
    private $started_at_time;

    /**
     * @var \DateTime
     */
    private $ended_at_time;


    /**
     * Set label
     *
     * @param string $label
     * @return WorkflowRun
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
     * Set started_at_time
     *
     * @param \DateTime $startedAtTime
     * @return WorkflowRun
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
     * @return WorkflowRun
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
     * Add processes
     *
     * @param \AppBundle\Entity\ProcessRun $processes
     * @return WorkflowRun
     */
    public function addProcesse(\AppBundle\Entity\ProcessRun $processes)
    {
        $this->processes[] = $processes;
    
        return $this;
    }

    /**
     * Remove processes
     *
     * @param \AppBundle\Entity\ProcessRun $processes
     */
    public function removeProcesse(\AppBundle\Entity\ProcessRun $processes)
    {
        $this->processes->removeElement($processes);
    }
}