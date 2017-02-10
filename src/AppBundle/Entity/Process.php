<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Process
 */
class Process
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $label;


    /**
     * Set description
     *
     * @param string $description
     * @return Process
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
     * Set label
     *
     * @param string $label
     * @return Process
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
     * @var \AppBundle\Entity\Workflow
     */
    private $workflow;


    /**
     * Set workflow
     *
     * @param \AppBundle\Entity\Workflow $workflow
     * @return Process
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
    private $uri;


    /**
     * Set uri
     *
     * @param string $uri
     * @return Process
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
    private $outputs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inputs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->outputs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inputs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add outputs
     *
     * @param \AppBundle\Entity\Output $outputs
     * @return Process
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
     * Add inputs
     *
     * @param \AppBundle\Entity\Input $inputs
     * @return Process
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
}