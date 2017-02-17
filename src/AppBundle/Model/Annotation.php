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
    
    public function findQualityAnnotationByElement($uri)
    {
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a w2share:QualityAnnotation.
            ?uri oa:hasTarget <".$uri.">.
            ?uri w2share:hasValue ?value.
            ?uri w2share:hasQualityDimension ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
        }"; 
        
        $quality_annotation_array = array();
        $quality_annotations = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_annotations); $i++)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
            $qualityAnnotation->setUri($quality_annotations[$i]['uri']['value']);
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($uri);
            
            $qualityAnnotation->setWorkflow($workflow);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_annotations[$i]['qualityDimension']['value']);
            $qualityDimension->setName($quality_annotations[$i]['qdName']['value']);
            
            $qualityAnnotation->setQualityDimension($qualityDimension);
            
            $qualityAnnotation->setValue($quality_annotations[$i]['value']['value']);
            
            $quality_annotation_array[] = $qualityAnnotation;  
        }
        
        $this->driver->getResults($query); 
        
        return $quality_annotation_array;
    }
    
    public function findAllQualityAnnotations() 
    {
        $query = 
        "SELECT * WHERE 
        {
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy ?creator.
           ?creator <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
        }";
        
        $quality_annotation_array = array();
        $quality_annotations = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_annotations); $i++)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
            $qualityAnnotation->setUri($quality_annotations[$i]['uri']['value']);
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($quality_annotations[$i]['element']['value']);
            
            $qualityAnnotation->setWorkflow($workflow);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_annotations[$i]['qualityDimension']['value']);
            $qualityDimension->setName($quality_annotations[$i]['qdName']['value']);
            
            $qualityAnnotation->setQualityDimension($qualityDimension);
            
            $qualityAnnotation->setValue($quality_annotations[$i]['value']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($quality_annotations[$i]['creator']['value']);
            $creator->setName($quality_annotations[$i]['creator_name']['value']);

            $qualityAnnotation->setCreator($creator);
            $qualityAnnotation->setCreatedAtTime($quality_annotations[$i]['annotatedAt']['value']);
            
            $quality_annotation_array[] = $qualityAnnotation;  
        }
       
        return $quality_annotation_array;
    }
    
    //TO-DO: Consulta com multiplos grafos
    public function findUsersWithQualityAnnotations()  
    {
        $query = 
        "SELECT * WHERE 
        {     
           ?element a w2share:QualityAnnotation;
           oa:annotatedBy ?creator.
           ?creator <foaf:name> ?name.
           
        }";
        
        $user_array = array();
        $user_quality_annotations = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($user_quality_annotations); $i++)
        {
            $person = new \AppBundle\Entity\Person();
            $person->setUri($user_quality_annotations[$i]['creator']['value']);
            $person->setName($user_quality_annotations[$i]['name']['value']);
            $user_array[$person->getUri()] = $person;
        }
        
        return $user_array;
        
    }
    
    public function findQualityAnnotationsByUser($user) 
    {
        $query = 
        "SELECT * WHERE 
        {            
           ?uri a w2share:QualityAnnotation;
           <oa:hasTarget> ?element;
           <w2share:hasValue> ?value;
           <oa:annotatedAt> ?annotatedAt;
           <oa:annotatedBy> <".$user->getUri().">. 
           <".$user->getUri()."> <foaf:name> ?creator_name.
           ?uri <w2share:hasQualityDimension> ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName.
        }";
        
        $quality_annotation_array = array();
        $quality_annotations = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_annotations); $i++)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
            $qualityAnnotation->setUri($quality_annotations[$i]['uri']['value']);
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($quality_annotations[$i]['element']['value']);
            
            $qualityAnnotation->setWorkflow($workflow);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_annotations[$i]['qualityDimension']['value']);
            $qualityDimension->setName($quality_annotations[$i]['qdName']['value']);
            
            $qualityAnnotation->setQualityDimension($qualityDimension);
            
            $qualityAnnotation->setValue($quality_annotations[$i]['value']['value']);
            $qualityAnnotation->setCreatedAtTime($quality_annotations[$i]['annotatedAt']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setName($quality_annotations[$i]['creator_name']['value']);
            $creator->setUri($user->getUri());

            $qualityAnnotation->setCreator($creator);
            
            $quality_annotation_array[] = $qualityAnnotation; 
        }
        
        return $quality_annotation_array;
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
