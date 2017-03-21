<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Model;
use AppBundle\Utils\Utils;

/**
 * Description of QualityMetric
 *
 * @author joana
 */
class QualityMetric 
{
    private $driver;
        
    public function __construct($driver)
    {
        $this->driver = $driver;
    }    
    
    public function insertQualityMetricToQualityDimension(\AppBundle\Entity\QualityDimension $qualityDimension, $metric, $description, $user) 
    {
        $now = new \Datetime();
        //Confirmar com Lucas
        $uri = Utils::convertNameToUri("Quality Metric", $qualityDimension->getName().'/'.$now->format('Ymdhis'));
        
        $qualityMetric = new \AppBundle\Entity\QualityMetric();
        $qualityMetric->setUri($uri);
        $qualityMetric->setQualityDimension($qualityDimension);
        
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitymetric')."> 
            { 
                <".$qualityMetric->getUri()."> a <w2share:QualityMetric>;
                <w2share:hasQualityDimension> <".$qualityDimension->getUri().">;
                <w2share:metric> '".$metric."';
                <rdfs:description> '".$description."';
                <dc:creator> <".$user->getUri().">.
            }
        }";
        
        $this->driver->getResults($query);    
        
        return $qualityMetric;
    }
    
    public function findQualityMetricsByDimension($uri)
    {
        $query = 
        "SELECT DISTINCT ?uri ?metric ?description ?qdName ?creator ?creator_name WHERE        
        { 
            ?uri a <w2share:QualityMetric>.
            ?uri <w2share:metric> ?metric.
            ?uri <rdfs:description> ?description.
            ?uri <w2share:hasQualityDimension> <".$uri.">.
            <".$uri."> <w2share:qdName> ?qdName.
            ?uri <dc:creator> ?creator.
            ?creator <foaf:name> ?creator_name.
        }";
        
        $quality_metric_array = array();
        $quality_metrics = $this->driver->getResults($query);  
        
        for ($i = 0; $i < count($quality_metrics); $i++)
        {
            $qualityMetric = new \AppBundle\Entity\QualityMetric();
            $qualityMetric->setUri($quality_metrics[$i]['uri']['value']);
            $qualityMetric->setMetric($quality_metrics[$i]['metric']['value']);
            $qualityMetric->setDescription($quality_metrics[$i]['description']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($quality_metrics[$i]['creator']['value']);
            $creator->setName($quality_metrics[$i]['creator_name']['value']);
            
            $qualityMetric->setCreator($creator);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($uri);
            $qualityDimension->setName($quality_metrics[$i]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            $quality_metric_array[] = $qualityMetric;
            
        }
        //echo $query; exit;
        $this->driver->getResults($query); 
        
        return $quality_metric_array;
        
    }

    public function findQualityMetric($uri)
    {   
        $query = 
        "SELECT * WHERE        
        { 
            <".$uri."> a <w2share:QualityMetric>.
            <".$uri."> <w2share:hasQualityDimension> ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            <".$uri."> <w2share:metric> ?metric.
            <".$uri."> <rdfs:description> ?description.
            <".$uri."> <dc:creator> ?creator.
            ?creator <foaf:name> ?creator_name.
        }";   
        
        $quality_metric = $this->driver->getResults($query);
        
        $qualityMetric = new \AppBundle\Entity\QualityMetric();
        
        if (count($quality_metric) > 0) 
        {
            $qualityMetric->setUri($uri);
            $qualityMetric->setMetric($quality_metric[0]['metric']['value']);
            $qualityMetric->setDescription($quality_metric[0]['description']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($quality_metric[0]['creator']['value']);
            $creator->setName($quality_metric[0]['creator_name']['value']);
            
            $qualityMetric->setCreator($creator);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_metric[0]['qualityDimension']['value']);
            $qualityDimension->setName($quality_metric[0]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            
            return $qualityMetric;
        } 
        
        return null;
    }
    
    public function findUsersWithQualityMetrics()
    {
        $query = 
        "SELECT * WHERE 
        {     
           ?uri a <w2share:QualityMetric>;
           <dc:creator> ?creator.
           ?creator <foaf:name> ?name.
           
        }";
        
        $user_array = array();
        $user_quality_metrics = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($user_quality_metrics); $i++)
        {
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($user_quality_metrics[$i]['creator']['value']);
            $creator->setName($user_quality_metrics[$i]['name']['value']);
            
            $user_array[$creator->getUri()] = $creator;
        }
        
        return $user_array;
    }
    
    public function findQualityMetricsByUser($user)
    {
        $query = 
        "SELECT * WHERE 
        {            
            ?uri a <w2share:QualityMetric>;
            <w2share:metric> ?metric;
            <rdfs:description> ?description;
            <w2share:hasQualityDimension> ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            ?uri <dc:creator> <".$user->getUri().">. 
            <".$user->getUri()."> <foaf:name> ?creator_name.
        }";
        
        $quality_metric_array = array();
        $quality_metrics = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_metrics); $i++)
        {
            $qualityMetric = new \AppBundle\Entity\QualityMetric();
            $qualityMetric->setUri($quality_metrics[$i]['uri']['value']);
            $qualityMetric->setMetric($quality_metrics[$i]['metric']['value']);
            $qualityMetric->setDescription($quality_metrics[$i]['description']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($user->getUri());
            $creator->setName($quality_metrics[$i]['creator_name']['value']);
            
            $qualityMetric->setCreator($creator);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_metrics[$i]['qualityDimension']['value']);
            $qualityDimension->setName($quality_metrics[$i]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            
            $quality_metric_array[] = $qualityMetric;
        }
        
        return $quality_metric_array;
    }
    
    public function findQualityMetricsByUserAndDimension($user, $qualitydimension_uri)
    {
        $query = 
        "SELECT DISTINCT ?uri ?metric ?description ?qdName ?creator_name WHERE 
        {            
            ?uri a <w2share:QualityMetric>;
            <w2share:metric> ?metric;
            <rdfs:description> ?description;
            <w2share:hasQualityDimension> <".$qualitydimension_uri.">.
            <".$qualitydimension_uri."> <w2share:qdName> ?qdName.
            ?uri <dc:creator> <".$user->getUri().">. 
            <".$user->getUri()."> <foaf:name> ?creator_name.
        }";
        
        $quality_metric_array = array();
        $quality_metrics = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_metrics); $i++)
        {
            $qualityMetric = new \AppBundle\Entity\QualityMetric();
            $qualityMetric->setUri($quality_metrics[$i]['uri']['value']);
            $qualityMetric->setMetric($quality_metrics[$i]['metric']['value']);
            $qualityMetric->setDescription($quality_metrics[$i]['description']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($user->getUri());
            $creator->setName($quality_metrics[$i]['creator_name']['value']);
            
            $qualityMetric->setCreator($creator);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($qualitydimension_uri);
            $qualityDimension->setName($quality_metrics[$i]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            
            $quality_metric_array[] = $qualityMetric;
        }
        
        return $quality_metric_array;
    }

    public function findAllQualityMetrics() 
    {
        $query = 
        "SELECT * WHERE 
        {
            ?uri a <w2share:QualityMetric>;
            <w2share:metric> ?metric;
            <rdfs:description> ?description;
            <w2share:hasQualityDimension> ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            ?uri <dc:creator> ?creator.
            ?creator <foaf:name> ?creator_name.

        }";
        
        $quality_metric_array = array();
        $quality_metrics = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_metrics); $i++)
        {
            $qualityMetric = new \AppBundle\Entity\QualityMetric();
            $qualityMetric->setUri($quality_metrics[$i]['uri']['value']);
            $qualityMetric->setMetric($quality_metrics[$i]['metric']['value']);
            $qualityMetric->setDescription($quality_metrics[$i]['description']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($quality_metrics[$i]['creator']['value']);
            $creator->setName($quality_metrics[$i]['creator_name']['value']);
            
            $qualityMetric->setCreator($creator);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_metrics[$i]['qualityDimension']['value']);
            $qualityDimension->setName($quality_metrics[$i]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            
            $quality_metric_array[$qualityMetric->getUri()] = $qualityMetric;  
        }
        
        return $quality_metric_array;
    }

        public function updateQualityMetric(\AppBundle\Entity\QualityMetric $qualityMetric, $user) 
    {
        $query = 
        " MODIFY <".$this->driver->getDefaultGraph('qualitymetric')."> 
            DELETE 
            { 
                <".$qualityMetric->getUri()."> a <w2share:QualityMetric>.
                <".$qualityMetric->getUri()."> <w2share:metric> ?metric.
                <".$qualityMetric->getUri()."> <rdfs:description> ?description.
                <".$qualityMetric->getUri()."> <w2share:hasQualityDimension> ?qualityDimension.
                <".$qualityMetric->getUri()."> <dc:creator> ?creator.
            }
            INSERT        
            { 
                <".$qualityMetric->getUri()."> a <w2share:QualityMetric>.
                <".$qualityMetric->getUri()."> <w2share:metric> '".$qualityMetric->getMetric()."'.
                <".$qualityMetric->getUri()."> <rdfs:description> '".$qualityMetric->getDescription()."'.
                <".$qualityMetric->getUri()."> <w2share:hasQualityDimension> <".$qualityMetric->getQualityDimension()->getUri().">.
                <".$qualityMetric->getUri()."> <dc:creator> <".$user->getUri().">.
                    
            }
            WHERE 
            { 
                <".$qualityMetric->getUri()."> a <w2share:QualityMetric>.
                <".$qualityMetric->getUri()."> <w2share:metric> ?metric.
                <".$qualityMetric->getUri()."> <rdfs:description> ?description.
                <".$qualityMetric->getUri()."> <w2share:hasQualityDimension> ?qualityDimension.
                <".$qualityMetric->getUri()."> <dc:creator> ?creator.
            }";
        
        return $this->driver->getResults($query);
    }
    
    public function deleteQualityMetric(\AppBundle\Entity\QualityMetric $qualityMetric)
    {
        $query = 
        "DELETE data FROM <".$this->driver->getDefaultGraph('qualitymetric')."> 
            {
                <".$qualityMetric->getUri()."> a <w2share:QualityMetric>.
                <".$qualityMetric->getUri()."> <w2share:metric> '".$qualityMetric->getMetric()."'.
                <".$qualityMetric->getUri()."> <rdfs:description> '".$qualityMetric->getDescription()."'.
                <".$qualityMetric->getUri()."> <w2share:hasQualityDimension> <".$qualityMetric->getQualityDimension()->getUri().">.
                <".$qualityMetric->getUri()."> <dc:creator> <".$qualityMetric->getQualityDimension()->getUri().">. 
            }";
        
        return $this->driver->getResults($query);
       
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('qualitymetric').">";        
        return $this->driver->getResults($query);
    }
    
}
