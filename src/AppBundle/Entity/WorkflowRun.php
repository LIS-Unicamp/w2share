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
     * @var \AppBundle\Entity\Workflow
     */
    private $workflow;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processes_run = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inputs_run = new \Doctrine\Common\Collections\ArrayCollection();
        $this->outputs_run = new \Doctrine\Common\Collections\ArrayCollection();
    }        
    
    /**
     * Set processes
     *
     * @param \Doctrine\Common\Collections\Collection 
     * @return WorkflowRun
     */
    public function setProcessesRun(array $processes)
    {        
        $this->processes_run = new \Doctrine\Common\Collections\ArrayCollection($processes);
    
        return $this;
    }      
    
    /**
     * Set inputs
     *
     * @param \Doctrine\Common\Collections\Collection 
     * @return WorkflowRun
     */
    public function setInputsRun(array $inputs)
    {        
        $this->inputs_run = new \Doctrine\Common\Collections\ArrayCollection($inputs);
    
        return $this;
    }    
    
    /**
     * Set outputs
     *
     * @param \Doctrine\Common\Collections\Collection 
     * @return WorkflowRun
     */
    public function setOutputsRun(array $outputs)
    {        
        $this->outputs_run = new \Doctrine\Common\Collections\ArrayCollection($outputs);
    
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $processes_run;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inputs_run;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $outputs_run;


    /**
     * Add processes_run
     *
     * @param \AppBundle\Entity\ProcessRun $processesRun
     * @return WorkflowRun
     */
    public function addProcessesRun(\AppBundle\Entity\ProcessRun $processesRun)
    {
        $this->processes_run[] = $processesRun;
    
        return $this;
    }

    /**
     * Remove processes_run
     *
     * @param \AppBundle\Entity\ProcessRun $processesRun
     */
    public function removeProcessesRun(\AppBundle\Entity\ProcessRun $processesRun)
    {
        $this->processes_run->removeElement($processesRun);
    }

    /**
     * Get processes_run
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcessesRun()
    {
        return $this->processes_run;
    }

    /**
     * Add inputs_run
     *
     * @param \AppBundle\Entity\InputRun $inputsRun
     * @return WorkflowRun
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
     * Add outputs_run
     *
     * @param \AppBundle\Entity\OutputRun $outputsRun
     * @return WorkflowRun
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
}