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
   * @var integer
   * Id da qualitydimension
   */
    private $id_qd;   
    
   
   /**
    * @var string
    */
    private $value;
    
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
  
  public function setId_Qd($id_qd) {
      $this->id_qd = $id_qd;
      return $this;
  }
  
  public function getId_Qd() {
      return $this->id_qd;
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
}