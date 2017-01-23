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
    * @var string
    */
    private $value;
    
    
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
   * 
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
    
}
