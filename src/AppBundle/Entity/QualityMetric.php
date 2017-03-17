<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * QualityMetric
 */
class QualityMetric {
    
    /**
     * @var string
     */
    private $metric;
    
    /**
     * @var string
     */
    private $description;
    
    
    /**
     * 
     * @param $metric
     */
    public function setMetric($metric) {
        $this->metric = $metric;
        
        return $this;
    }
    /**
     * 
     * @return string
     */
    public function getMetric() {
        return $this->metric;
    }

    /**
     * 
     * @param $description
     */
    public function setDescription($description) {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $result;

    /**
     * @var \AppBundle\Entity\QualityDimension
     */
    private $quality_dimension;


    /**
     * Set uri
     *
     * @param string $uri
     * @return QualityMetric
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
     * Set result
     *
     * @param string $result
     * @return QualityMetric
     */
    public function setResult($result)
    {
        $this->result = $result;
    
        return $this;
    }

    /**
     * Get result
     *
     * @return string 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set quality_dimension
     *
     * @param \AppBundle\Entity\QualityDimension $qualityDimension
     * @return QualityMetric
     */
    public function setQualityDimension(\AppBundle\Entity\QualityDimension $qualityDimension = null)
    {
        $this->quality_dimension = $qualityDimension;
    
        return $this;
    }

    /**
     * Get quality_dimension
     *
     * @return \AppBundle\Entity\QualityDimension 
     */
    public function getQualityDimension()
    {
        return $this->quality_dimension;
    }
    /**
     * @var \AppBundle\Entity\Person
     */
    private $creator;


    /**
     * Set creator
     *
     * @param \AppBundle\Entity\Person $creator
     * @return QualityMetric
     */
    public function setCreator(\AppBundle\Entity\Person $creator = null)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\Person 
     */
    public function getCreator()
    {
        return $this->creator;
    }
    
    //TODO
    public function __toString() {
        return $this->description;
    }
    
}