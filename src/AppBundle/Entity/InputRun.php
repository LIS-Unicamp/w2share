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
     * @var \AppBundle\Entity\ProcessRun
     */
    private $process;


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
     * Set process
     *
     * @param \AppBundle\Entity\ProcessRun $process
     * @return InputRun
     */
    public function setProcess(\AppBundle\Entity\ProcessRun $process = null)
    {
        $this->process = $process;
    
        return $this;
    }

    /**
     * Get process
     *
     * @return \AppBundle\Entity\ProcessRun 
     */
    public function getProcess()
    {
        return $this->process;
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
}