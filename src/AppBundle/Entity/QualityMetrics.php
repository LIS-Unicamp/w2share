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
class QualityMetrics {
    
    private $workflow;
    
    /**
     * @var string
     */
    private $title;
    
    /**
     * @var string
     */
    private $description;
    
    /**
     * 
     * @param $workflow
     */
    public function setWorkflow(Workflow $workflow) {
        $this->workflow = $workflow;
    }
    
    /**
     * 
     * @return workflow
     */
    public function getWorkflow() {
        return $this->workflow;
    }
    
    
    /**
     * 
     * @param type $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    /**
     * 
     * @return string
     */
    public function getTitle() {
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
    
}