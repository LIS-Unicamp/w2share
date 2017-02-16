<?php
namespace AppBundle\Model;
use AppBundle\Utils\Utils;

/**
 * Description of Provenance
 *
 * @author lucas
 */
class Annotation
{
    private $driver;
    
    
    public function __construct($driver)
    {
        $this->driver = $driver;
    }
    
    public function ontology()
    {
        return array('http://purl.org/dc/terms/dcterms:description');
    }
    
    public function insertAnnotation($subject, $property, $object)
    {
        $now = new \Datetime();
        if (strpos("<", $object) === false)
        {
            $object = "\"".$object."\"";
        }
        
        $query1 = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('annotations')."> 
            {
                ?annotation a oa:Annotation ;
                oa:hasTarget <".$subject.">;
                oa:hasBody ?body.
            }
        }";
        $array = $this->driver->getSingleResult($query1,true);
        
        if ($array)
        {
            $body = $array['body'];
            
            $query2 = 
            "INSERT        
            { 
                GRAPH <".$this->driver->getDefaultGraph('annotations')."> 
                { 
                    <".$body."> <".$property."> ".$object.".
                }
            }";
        }
        else
        {
            $query2 = 
            "INSERT        
            { 
                GRAPH <".$this->driver->getDefaultGraph('annotations')."> 
                { 
                    _:annotation a oa:Annotation ;
                    oa:hasTarget <".$subject.">;
                    oa:hasBody [<".$property."> ".$object."];
                    oa:motivatedBy oa:tagging ;
                    oa:annotatedAt \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\";
                    oa:serializedAt \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\".
                }
            }";
        }
                
        return $this->driver->getResults($query2);
    }
    
    public function listAnnotations($subject)
    {                
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('annotations')."> 
            { 
                _:annotation a oa:Annotation ;
                oa:hasTarget <".$subject.">;
                oa:hasBody ?body;
                oa:annotatedAt ?annotatedAt.
                ?body ?property ?object.
                OPTIONAL { <".$subject."> oa:annotatedBy ?person }
            } 
        }";
        return $this->driver->getResults($query);
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('annotations').">";        
        return $this->driver->getResults($query);                  
    }
    
    public function insertQualityAnnotation(\AppBundle\Entity\Workflow $workflow, \AppBundle\Entity\QualityDimension $qualityDimension, $value, $user)
    {   
        $now = new \Datetime();
        $uri = Utils::convertNameToUri("Quality Annotation", $qualityDimension->getName().'/'.$now->format('Ymdhis'));
        
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        $qualityAnnotation->setUri($uri);
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension-annotation')."> 
            { 
                <".$qualityAnnotation->getUri()."> a w2share:QualityAnnotation;
                oa:hasTarget <".$workflow->getUri().">;
                w2share:hasQualityDimension <".$qualityDimension->getUri().">;
                w2share:hasValue '".$value."';
                oa:annotatedAt \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\";
                oa:annotatedBy <".$user->getUri().">. 
            }
        }";
        $this->driver->getResults($query);    
        return $qualityAnnotation;
    }
    
    public function findQualityAnnotationByObject($uri)
    {
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension-annotation')."> 
            { 
                ?quality_annotation a <w2share:QualityAnnotation>;
                <oa:hasTarget> <".$uri.">;
                <w2share:hasQualityDimension> ?quality_dimension;
                <w2share:hasValue> ?value;
                oa:annotatedAt ?annotatedAt;
                oa:annotatedBy ?creator.
            }
        }"; 
        
        return $this->driver->getResults($query); 
    }
    
    public function clearGraphQualityAnnotation()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('qualitydimension-annotation').">";        
        return $this->driver->getResults($query);                  
    }
    
}

//TO-DO
//Listar as qualityannotations que existem
//No formulario listar as qualityannotation para o workflow/processo/resultado/fonte
//um clear graph mais robusto para apagar todos os grafos
