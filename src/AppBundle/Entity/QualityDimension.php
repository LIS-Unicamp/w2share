<?php

namespace AppBundle\Entity;

/**
 * QualityFlow
 */


class QualityDimension {

    /**
     * @var string
     */
      private $uri;   

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
    private $valueType;

    /**
    * @var string
    */
    private $creator;

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
     * Get uri
     *
     * @return strin
     */
    public function getUri()
    {
        return $this->uri;
    }    

    /**
     * Get uri
     *
     * @return string
     */
    public function setUri($uri)
    {
       $this->uri = $uri;

       return $this;

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
      * @param string $valueType
      *
      * @return string
      */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;
       
        return $this;
    }

     /**
      * Get description
      *
      * @return string
      */
    public function getValueType()
    {
        return $this->valueType;
    }
}
