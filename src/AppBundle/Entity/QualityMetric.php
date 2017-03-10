<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * QualityMetrics
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
     * @param type $title
     */
    public function setMetric($metric) {
        $this->title = $title;
    }
    /**
     * 
     * @return string
     */
    public function getMetric() {
        return $this->title;
    }

    /**
     * 
     * @param $description
     */
    public function setDescription($description) {
        $this->description = $description;
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
     * @var string
     */
    private $creator;

    /**
     * @var \AppBundle\Entity\QualityDimension
     */
    private $quality_dimension;


    /**
     * Set uri
     *
     * @param string $uri
     * @return QualityMetrics
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
     * @return QualityMetrics
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
     * Set creator
     *
     * @param string $creator
     * @return QualityMetrics
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return string 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set quality_dimension
     *
     * @param \AppBundle\Entity\QualityDimension $qualityDimension
     * @return QualityMetrics
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
}