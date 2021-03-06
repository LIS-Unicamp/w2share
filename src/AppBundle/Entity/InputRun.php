<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InputRun
 */
class InputRun
{
    /**
     * @var string
     */
    private $uri;   


    /**
     * Set uri
     *
     * @param string $uri
     * @return InputRun
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
    private $content;


    /**
     * Set content
     *
     * @param string $content
     * @return InputRun
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
   
    /**
     * @var \AppBundle\Entity\ProcessRun
     */
    private $process_run;

    /**
     * @var \AppBundle\Entity\WorkflowRun
     */
    private $workflow_run;


    /**
     * Set process_run
     *
     * @param \AppBundle\Entity\ProcessRun $processRun
     * @return InputRun
     */
    public function setProcessRun(\AppBundle\Entity\ProcessRun $processRun = null)
    {
        $this->process_run = $processRun;
    
        return $this;
    }

    /**
     * Get process_run
     *
     * @return \AppBundle\Entity\ProcessRun 
     */
    public function getProcessRun()
    {
        return $this->process_run;
    }

    /**
     * Set workflow_run
     *
     * @param \AppBundle\Entity\WorkflowRun $workflowRun
     * @return InputRun
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
     * @var \AppBundle\Entity\Input
     */
    private $input;


    /**
     * Set input
     *
     * @param \AppBundle\Entity\Input $input
     * @return InputRun
     */
    public function setInput(\AppBundle\Entity\Input $input = null)
    {
        $this->input = $input;
    
        return $this;
    }

    /**
     * Get input
     *
     * @return \AppBundle\Entity\Input 
     */
    public function getInput()
    {
        return $this->input;
    }
}