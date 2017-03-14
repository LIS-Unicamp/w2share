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
                <dc:creator> <".$user->getUri().">;
                <dc:date> \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\".
            }
        }";
        
        $this->driver->getResults($query);    
        
        return $qualityMetric;
    }
    
    public function findQualityMetricByDimension($uri)
    {
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a <w2share:QualityMetric>.
                ?uri <w2share:hasQualityDimension> <".$uri.">.
                <".$uri."> <w2share:qdName> ?qdName.
                ?uri <w2share:metric> ?metric.
                ?uri <rdfs:description> ?description.
                ?uri <dc:creator> ?creator.
                ?creator <foaf:name> ?creator_name.
        }";
        
        $quality_metric_array = array();
        $quality_metrics = $this->driver->getResults($query);  
        
        for ($i = 0; $i < count($quality_metrics); $i++)
        {
            $qualityMetric = new \AppBundle\Entity\QualityMetric();
            $qualityMetric->setUri($quality_metrics[$i]['uri']['value']);
            //TODO: Verificar por que nao esta adicionando a metrica
            $qualityMetric->setMetric($quality_metrics[$i]['metric']['value']);
            $qualityMetric->setDescription($quality_metrics[$i]['description']['value']);
            //TODO: Na entidade QualityMetric criar o objeto Creator.
            $qualityMetric->setCreator($quality_metrics[$i]['creator_name']['value']);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($uri);
            $qualityDimension->setName($quality_metrics[$i]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            $quality_metric_array[] = $qualityMetric;
            
        }
        
        $this->driver->getResults($query); 
        
        return $quality_metric_array;
        
    }

    public function findQualityMetricByUri($uri)
    {   
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitymetric')."> 
            { 
                <".$uri."> a <w2share:QualityMetric>.
                <".$uri."> <w2share:hasQualityDimension> ?qualityDimension.
                <".$uri."> <w2share:metric> ?metric.
                <".$uri."> <rdfs:description> ?description.
                <".$uri."> <dc:creator> ?creator.
                <".$uri."> <dc:date> ?date.
                    
            }
        }";   
        
        $quality_metric = $this->driver->getResults($query);
        
        $qualityMetric = new \AppBundle\Entity\QualityMetric();
        
        try {
            $qualityMetric->setUri($uri);
            $qualityMetric->setMetric($quality_metric[0]['metric']['value']);
            $qualityMetric->setDescription($quality_metric[0]['description']['value']);
            
        } catch (\Symfony\Component\Debug\Exception\ContextErrorException $ex) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Metric not found!");
        }
        
        return $qualityMetric;
    }
    
}
