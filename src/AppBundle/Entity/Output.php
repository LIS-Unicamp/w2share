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
}