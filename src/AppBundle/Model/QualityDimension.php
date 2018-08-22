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
    
    public function insertQualityDimension(\AppBundle\Entity\QualityDimension $qd, $user) 
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
                <".$qd->getUri()."> <dc:creator> <".$user->getUri().">. 
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
            ?uri a <w2share:QualityDimension>;
            <w2share:qdName> ?name;
            <w2share:valueType> ?valueType;
            <rdfs:description> ?description;
            <dc:creator> ?creator.
            ?creator <foaf:name> ?creator_name.
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
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setName($quality_dimensions[$i]['creator_name']['value']);
            $creator->setUri($quality_dimensions[$i]['creator']['value']);

            $qualityDimension->setCreator($creator);
            
            $quality_dimension_array[$qualityDimension->getUri()] = $qualityDimension;  
        }
        
        return $quality_dimension_array;
    }
    
    public function findAllQualityDimensionsForm() 
    {
        $query = 
        "SELECT * WHERE 
        {
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
            {
                ?uri a <w2share:QualityDimension>;
                <w2share:qdName> ?name.
            }
        }";
        
        $quality_dimension_array = array();
        $quality_dimensions = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_dimensions); $i++)
        {           
            $quality_dimension_array[$quality_dimensions[$i]['uri']['value']] = $quality_dimensions[$i]['name']['value'];  
        }
        
        return $quality_dimension_array;
    }
    
    /*
     * Find quality dimensions from an specific user
     */
    public function findQualityDimensionsByUser($user) 
    {
        $query = 
        "SELECT * WHERE 
        {            
            ?uri a <w2share:QualityDimension>;
            <w2share:qdName> ?name;
            <w2share:valueType> ?valueType;
            <rdfs:description> ?description;
            <dc:creator> <".$user->getUri().">. 
            <".$user->getUri()."> <foaf:name> ?creator_name.
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
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setName($quality_dimensions[$i]['creator_name']['value']);
            $creator->setUri($user->getUri());

            $qualityDimension->setCreator($creator);
            
            $quality_dimension_array[] = $qualityDimension;  
        }
        
        return $quality_dimension_array;
    }
    //TO-DO: Consulta com multiplos grafos
    public function findUsersWithQualityDimensions()  
    {
        $query = 
        "SELECT * WHERE 
        {     
           ?element a <w2share:QualityDimension>;
           <dc:creator> ?creator.
           ?creator <foaf:name> ?name.
           
        }";
        
        $user_array = array();
        $user_quality_dimensions = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($user_quality_dimensions); $i++)
        {
            $person = new \AppBundle\Entity\Person();
            $person->setUri($user_quality_dimensions[$i]['creator']['value']);
            $person->setName($user_quality_dimensions[$i]['name']['value']);
            $user_array[$person->getUri()] = $person;
        }
        
        return $user_array;
        
    }
    
    public function deleteQualityDimension(\AppBundle\Entity\QualityDimension $qd)
    {

        $query = 
        "DELETE data FROM <".$this->driver->getDefaultGraph('qualitydimension')."> 
            {
                <".$qd->getUri()."> a <w2share:QualityDimension>.
                <".$qd->getUri()."> <w2share:qdName> '".$qd->getName()."'.
                <".$qd->getUri()."> <w2share:valueType> '".$qd->getValueType()."'.
                <".$qd->getUri()."> <rdfs:description> '".$qd->getDescription()."'.
            }";
        
        return $this->driver->getResults($query);
    }
    

    public function updateQualityDimension(\AppBundle\Entity\QualityDimension $qd) 
    {        
        $uri = Utils::convertNameToUri("Quality Dimension", $qd->getName());
        $qd->setUri($uri);
        $query = 
        "   MODIFY <".$this->driver->getDefaultGraph('qualitydimension')."> 
            DELETE 
            { 
                <".$qd->getUri()."> a <w2share:QualityDimension>.
                <".$qd->getUri()."> <w2share:qdName> ?name.
                <".$qd->getUri()."> <w2share:valueType> ?valueType.
                <".$qd->getUri()."> <rdfs:description> ?description.
            }
            INSERT        
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
            }";
        
        return $this->driver->getResults($query);
        
    }


    public function qualityDimensionBeingUsed(\AppBundle\Entity\QualityDimension $qd)
    {
        $query = "SELECT ?qdt WHERE
        { ?qdt <w2share:describesQualityDimension> <".$qd->getUri() . "> . 
        }";
        if (count( $this->driver->getResults($query)) > 0) {
            return true;
        }
        return false;
    }

    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('qualitydimension').">";        
        return $this->driver->getResults($query);                  
    }
    
}
