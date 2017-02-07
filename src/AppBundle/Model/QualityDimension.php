<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Model;
use AppBundle\Utils\Utils;
/**
 * Description of QualityFlow
 *
 * @author joana
 */
class QualityDimension 
{
    private $driver;
        
    public function __construct($driver)
    {
        $this->driver = $driver;
    }
    
    public function clearDB ()
    {
        $this->em->createQuery('DELETE FROM AppBundle:QualityDimension')->execute();
    }
    
    public function insertQualityDimension(\AppBundle\Entity\QualityDimension $qd) 
    { 
        $uri = Utils::convertNameToUri("Quality Dimension", $qd->getName());
        $qd->setUri($uri);
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
            { 
                <".$qd->getUri()."> a <w2share:QualityDimension>.
                <".$qd->getUri()."> <w2share:qdName> '".$qd->getName()."'.
                <".$qd->getUri()."> <w2share:valueType> '".$qd->getValueType()."'.
                <".$qd->getUri()."> <rdfs:description> '".$qd->getDescription()."'.
            }
        }";  

        return $this->driver->getResults($query);        
    }
    
    public function findOneQualityDimension($uri) 
    { 
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
            { 
                <".$uri."> a <w2share:QualityDimension>.
                <".$uri."> <w2share:qdName> ?name.
                <".$uri."> <w2share:valueType> ?valueType.
                <".$uri."> <rdfs:description> ?description.
            }
        }";   
        
        $quality_dimension = $this->driver->getResults($query);
        
        $qualityDimension = new \AppBundle\Entity\QualityDimension();
        try {
            $qualityDimension->setUri($uri);
            $qualityDimension->setName($quality_dimension[0]['name']['value']);
            $qualityDimension->setDescription($quality_dimension[0]['description']['value']);
            $qualityDimension->setValueType($quality_dimension[0]['valueType']['value']);
        } catch (\Symfony\Component\Debug\Exception\ContextErrorException $ex) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Dimension not found!");
        }
        
        return $qualityDimension;        
    }
    
    public function findAllQualityDimensions() 
    {
        $query = 
        "SELECT * WHERE 
        {
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
             {
                ?uri a <w2share:QualityDimension>;
                <w2share:qdName> ?name;
                <w2share:valueType> ?valueType;
                <rdfs:description> ?description.
            }
        }";
        
        $quality_dimension_array = array();
        $quality_dimensions = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_dimensions); $i++)
        {
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_dimensions[$i]['uri']['value']);
            $qualityDimension->setName($quality_dimensions[$i]['name']['value']);
            $qualityDimension->setDescription($quality_dimensions[$i]['description']['value']);
            $qualityDimension->setValueType($quality_dimensions[$i]['valueType']['value']);
            
            $quality_dimension_array[] = $qualityDimension;  
        }
        
        return $quality_dimension_array;
    }
    
    public function deleteQualityDimension(\AppBundle\Entity\QualityDimension $qd)
    {
        $query = 
        "DELETE data FROM <".$this->driver->getDefaultGraph('qualitydimension')."> {
                <".$qd->getUri()."> a w2share:QualityDimension
                <".$qd->getUri()."> <w2share:qdName> '".$qd->getName()."'.
                <".$qd->getUri()."> <w2share:valueType> '".$qd->getValueType()."'.
                <".$qd->getUri()."> <rdfs:description> '".$qd->getDescription()."'.
        }";
        return $this->driver->getResults($query);        
    }
    

    public function updateQualityDimension(\AppBundle\Entity\QualityDimension $qd) 
    {        
        $this->deleteQualityDimension($qd);
        
        $uri = Utils::convertNameToUri("Quality Dimension", $qd->getName());
        $qd->setUri($uri);
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
            { 
                <".$qd->getUri()."> a <w2share:QualityDimension>.
                <".$qd->getUri()."> <w2share:qdName> '".$qd->getName()."'.
                <".$qd->getUri()."> <w2share:valueType> '".$qd->getValueType()."'.
                <".$qd->getUri()."> <rdfs:description> '".$qd->getDescription()."'.
            }
            WHERE 
            { 
                <".$qd->getUri()."> a <w2share:QualityDimension>.
                <".$qd->getUri()."> <w2share:qdName> ?name.
                <".$qd->getUri()."> <w2share:valueType> ?valueType.
                <".$qd->getUri()."> <rdfs:description> ?description.
            }
        }";
        return $this->driver->getResults($query);
        
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('qualitydimension').">";        
        return $this->driver->getResults($query);                  
    }
    
}
