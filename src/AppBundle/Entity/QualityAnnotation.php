<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * QualityAnnotation
 */
class QualityAnnotation {
    
   /**
    * @var Object
    */
    private $qualityDimension;
 
  /**
   * @var integer
   */
    private $id;   
    

   /**
    * @var string
    */
    private $value;
    
    /**
    * @var string
    */
    private $creator;
    
    /**
    * get $qualityDimension
    * 
    * @return QualityDimension
    */
  public function getQualityDimension()
  {
        return $this->qualityDimension;
  }
    
   /**
    * Set $qualityDimension
    *
    * @param string $qualityDimension
    *
    * @return QualityDimension
    */
  public function setQualityDimension(QualityDimension $qualityDimension)
  {
    $this->qualityDimension = $qualityDimension;

      return $this;
  }
  
  /**
   * @param $value
   * @return value
   * 
   */
  public function setValue($value) {
      $this->value = $value;
      
      return $this;
  }
  
  /**
   * 
   * @return string
   */
  public function getValue() {
      
      return $this->value;
      
  }
    
    /**
     * @var string
     */
    private $uri;

    /**
     * @var \DateTime
     */
    private $created_at_time;

    /**
     * @var \AppBundle\Entity\QualityDimension
     */
    private $quality_dimension;

    /**
     * @var \AppBundle\Entity\Workflow
     */
    private $workflow;


    /**
     * Set uri
     *
     * @param string $uri
     * @return QualityAnnotation
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
     * Set created_at_time
     *
     * @param \DateTime $createdAtTime
     * @return QualityAnnotation
     */
    public function setCreatedAtTime($createdAtTime)
    {
        $this->created_at_time = $createdAtTime;
    
        return $this;
    }

    /**
     * Get created_at_time
     *
     * @return \DateTime 
     */
    public function getCreatedAtTime()
    {
        return $this->created_at_time;
    }

    /**
     * Set workflow
     *
     * @param \AppBundle\Entity\Workflow $workflow
     * @return QualityAnnotation
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
    * Set creator
    *
    * @param Person $creator
    *
    * @return Person
    */
    public function setCreator(Person $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get the creator
     *
     * @return Person
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @var \AppBundle\Entity\ProcessRun
     */
    private $process_run;

    /**
     * @var \AppBundle\Entity\OutputRun
     */
    private $output_run;


    /**
     * Set process_run
     *
     * @param \AppBundle\Entity\ProcessRun $processRun
     * @return QualityAnnotation
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
     * Set output_run
     *
     * @param \AppBundle\Entity\OutputRun $outputRun
     * @return QualityAnnotation
     */
    public function setOutputRun(\AppBundle\Entity\OutputRun $outputRun = null)
    {
        $this->output_run = $outputRun;
    
        return $this;
    }

    /**
     * Get output_run
     *
     * @return \AppBundle\Entity\OutputRun 
     */
    public function getOutputRun()
    {
        return $this->output_run;
    }
}