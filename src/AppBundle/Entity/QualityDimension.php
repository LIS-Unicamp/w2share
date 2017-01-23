<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * QualityFlow
 */


class QualityDimension {

  /**
   * @var integer
   */
    private $id;   
    
  /**
   * @var string
   */
  private $name;
  
  
  /**
   * @var string
   */
  private $description;
  
 /**
  * @var string
  */
  private $value_type;
  
/**
 * Get id
 *
 * @return integer
 */
 public function getId()
 {
        return $this->id;
 }     
 
 
/**
 * Set name
 *
 * @param string $name
 *
 * @return Name
 */
  public function setName($name)
  {
    $this->name = $name;

      return $this;
  }

 /**
  * Get name of the quality dimension
  *
  * @return string
  */
 public function getName()
 {
        return $this->name;
 }
    
 /**
  * Set description
  *
  * @param string $description
  *
  * @return Description
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
  * Set value_type
  *
  * @param string $value_type
  *
  * @return Value_Type
  */
public function setValue_Type($value_type)
 {
    $this->value_type = $value_type;

       return $this;
 }

 /**
  * Get description
  *
  * @return string
  */
 public function geValue_Type()
 {
    return $this->description;
 }

 
}
