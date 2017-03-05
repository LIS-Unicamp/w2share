<?php

namespace AppBundle\Entity;

/**
 * Output
 */
class Output
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
     * @return Output
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
     * @return Output
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
     * @var string
     */
    private $uri;


    /**
     * Set uri
     *
     * @param string $uri
     * @return Output
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
     * @var \AppBundle\Entity\Process
     */
    private $process;


    /**
     * Set process
     *
     * @param \AppBundle\Entity\Process $process
     * @return Output
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
     * @var string
     */
    private $example_data;

    /**
     * @var \AppBundle\Entity\Workflow
     */
    private $workflow;


    /**
     * Set example_data
     *
     * @param string $exampleData
     * @return Output
     */
    public function setExampleData($exampleData)
    {
        $this->example_data = $exampleData;
    
        return $this;
    }

    /**
     * Get example_data
     *
     * @return string 
     */
    public function getExampleData()
    {
        return $this->example_data;
    }

    /**
     * Set workflow
     *
     * @param \AppBundle\Entity\Workflow $workflow
     * @return Output
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
     * @var \AppBundle\Entity\Output
     */
    private $output;


    /**
     * Set output
     *
     * @param \AppBundle\Entity\Output $output
     * @return Output
     */
    public function setOutput(\AppBundle\Entity\Output $output = null)
    {
        $this->output = $output;
    
        return $this;
    }

    /**
     * Get output
     *
     * @return \AppBundle\Entity\Output 
     */
    public function getOutput()
    {
        return $this->output;
    }
}