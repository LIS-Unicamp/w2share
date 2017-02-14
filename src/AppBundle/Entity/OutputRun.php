<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OutputRun
 */
class OutputRun
{
    /**
     * @var string
     */
    private $uri;

    /**
     * Set uri
     *
     * @param string $uri
     * @return OutputRun
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
     * @return OutputRun
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
     * @return OutputRun
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
     * @return OutputRun
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
}