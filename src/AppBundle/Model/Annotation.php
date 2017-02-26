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
    
    
    public function insertQualityAnnotationToElement($element_uri, $type, \AppBundle\Entity\QualityDimension $qualityDimension, $value, $user)
    {   //$element_uri = $object->getUri();
       
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
                oa:hasTarget <".$element_uri.">;
                w2share:hasQualityDimension <".$qualityDimension->getUri().">;
                w2share:hasValue '".$value."';
                oa:annotatedAt \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\";
                oa:annotatedBy <".$user->getUri().">. 
            }
        }";
        
        $this->driver->getResults($query);    
        
        return $qualityAnnotation;
    }
    
    public function findQualityAnnotationByURI($uri, $type)
    {
        $query = 
        "SELECT * WHERE        
        { 
            <".$uri."> a w2share:QualityAnnotation.
            <".$uri."> oa:hasTarget ?element.
            <".$uri."> w2share:hasValue ?value.
            <".$uri."> w2share:hasQualityDimension ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            
        }";
       
        $quality_annotation = $this->driver->getResults($query);
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        if (count($quality_annotation) > 0)
        {   
            $qualityAnnotation->setUri($uri);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_annotation[0]['qualityDimension']['value']);
            $qualityDimension->setName($quality_annotation[0]['qdName']['value']);
            
            $qualityAnnotation->setQualityDimension($qualityDimension);
            
            $qualityAnnotation->setValue($quality_annotation[0]['value']['value']);
            
            switch ($type)
            {
                case 'workflow':
                    $workflow = new \AppBundle\Entity\Workflow();
                    $workflow->setUri($quality_annotation[0]['element']['value']);

                    $qualityAnnotation->setWorkflow($workflow);
                    break;
                case 'process_run':
                    $process_run = new \AppBundle\Entity\ProcessRun();
                    $process_run->setUri($quality_annotation[0]['element']['value']);
                    
                    $qualityAnnotation->setProcessRun($process_run);
                    break;
                case 'output_run':
                    $output_run = new \AppBundle\Entity\OutputRun();
                    $output_run->setUri($quality_annotation[0]['element']['value']);
                    
                    $qualityAnnotation->setOutputRun($output_run);
                    break;
            }
            
            return $qualityAnnotation;  
        } 
        
        return null;
    }
    
    public function findQualityAnnotationByElement($uri, $type)
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
            
            switch ($type)
            {
                case 'workflow':
                    $workflow = new \AppBundle\Entity\Workflow();
                    $workflow->setUri($uri);

                    $qualityAnnotation->setWorkflow($workflow);
                    break;
                case 'process_run':
                    $process_run = new \AppBundle\Entity\ProcessRun();
                    $process_run->setUri($uri);
                    
                    $qualityAnnotation->setProcessRun($process_run);
                    break;
                case 'output_run':
                    $output_run = new \AppBundle\Entity\OutputRun();
                    $output_run->setUri($uri);
                    
                    $qualityAnnotation->setOutputRun($output_run);
                    break;
            }
            
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
        "SELECT DISTINCT ?uri ?element ?type ?content ?creator ?creator_name ?annotatedBy
            ?qualityDimension ?qdName ?annotatedAt ?value WHERE {
        {
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy ?creator.
           ?element rdf:type ?type.
           FILTER regex(?type, \"Workflow\", \"i\" )
           ?creator <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
        }
        UNION 
        {
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy ?creator.
           ?element rdf:type ?type.
           FILTER regex(?type, \"ProcessRun\", \"i\" )
           ?creator <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
        }
        UNION { 
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy ?creator.
           ?element wfprov:describedByParameter ?output.
           ?output rdf:type ?type.
           FILTER regex(?type, \"Output\", \"i\" )
           OPTIONAL {?output tavernaprov:content ?content.}
           ?creator <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
            }
        }";
        
        $quality_annotation_array = array();
        $quality_annotations = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_annotations); $i++)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
            $qualityAnnotation->setUri($quality_annotations[$i]['uri']['value']);
            
            $type = $quality_annotations[$i]['type']['value'];
                       
            switch ($type)
            {
                case 'http://purl.org/wf4ever/wfdesc#Workflow':                   
                    $workflow = new \AppBundle\Entity\Workflow();
                    $workflow->setUri($quality_annotations[$i]['element']['value']);          
                    
                    $qualityAnnotation->setWorkflow($workflow);  
                    break;
                case 'http://purl.org/wf4ever/wfprov#ProcessRun':                                                     
                    $process_run = new \AppBundle\Entity\ProcessRun();                  
                    $process_run->setUri($quality_annotations[$i]['element']['value']);
                                        
                    $qualityAnnotation->setProcessRun($process_run);
                    break;                    
                default:
                    $output_data_run = new \AppBundle\Entity\OutputRun();
                    $output_data_run->setUri($quality_annotations[$i]['element']['value']);                    
                    
                    $qualityAnnotation->setOutputRun($output_data_run);
                    break;
            }
            
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
        
        $this->driver->getResults($query);  
        
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
        "SELECT DISTINCT ?uri ?element ?type ?content ?creator ?creator_name ?annotatedBy
            ?qualityDimension ?qdName ?annotatedAt ?value WHERE {
        {
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy <".$user->getUri().">.
           ?element rdf:type ?type.
           FILTER regex(?type, \"Workflow\", \"i\" )
           <".$user->getUri()."> <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
        }
        UNION 
        {
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy <".$user->getUri().">.
           ?element rdf:type ?type.
           FILTER regex(?type, \"ProcessRun\", \"i\" )
           <".$user->getUri()."> <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
        }
        UNION { 
           ?uri a w2share:QualityAnnotation;
           oa:hasTarget ?element;
           w2share:hasValue ?value;
           oa:annotatedAt ?annotatedAt;
           oa:annotatedBy <".$user->getUri().">.
           ?element wfprov:describedByParameter ?output.
           ?output rdf:type ?type.
           FILTER regex(?type, \"Output\", \"i\" )
           OPTIONAL {?output tavernaprov:content ?content.}
           <".$user->getUri()."> <foaf:name> ?creator_name.
           ?uri w2share:hasQualityDimension ?qualityDimension.
           ?qualityDimension <w2share:qdName> ?qdName. 
            }
        }";
        
        $quality_annotation_array = array();
        $quality_annotations = $this->driver->getResults($query);                
        
        for ($i = 0; $i < count($quality_annotations); $i++)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
            $qualityAnnotation->setUri($quality_annotations[$i]['uri']['value']);
            
            $type = $quality_annotations[$i]['type']['value'];
                                   
            switch ($type)
            {
                case 'http://purl.org/wf4ever/wfdesc#Workflow':                   
                    $workflow = new \AppBundle\Entity\Workflow();
                    $workflow->setUri($quality_annotations[$i]['element']['value']);          
                    
                    $qualityAnnotation->setWorkflow($workflow);  
                    break;
                case 'http://purl.org/wf4ever/wfprov#ProcessRun':                                                     
                    $process_run = new \AppBundle\Entity\ProcessRun();                  
                    $process_run->setUri($quality_annotations[$i]['element']['value']);
                                        
                    $qualityAnnotation->setProcessRun($process_run);
                    break;                    
                default:
                    //http://purl.org/wf4ever/wfdesc#Output 
                    $output_data_run = new \AppBundle\Entity\OutputRun();
                    $output_data_run->setUri($quality_annotations[$i]['element']['value']);                    
                    
                    $qualityAnnotation->setOutputRun($output_data_run);
                    break;
            }
            
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
        
        $this->driver->getResults($query); 
        
        return $quality_annotation_array;
    }
    
    public function updateQualityAnnotation(\AppBundle\Entity\QualityAnnotation $qualityAnnotation, $type) 
    {        
        $uri = $qualityAnnotation->getUri();
        $element_uri = "";
        
        switch ($type)
        {
            case 'workflow':
                $element_uri = $qualityAnnotation->getWorkflow()->getUri();
                break;
            case 'process_run':
                $element_uri = $qualityAnnotation->getProcessRun()->getUri();
                break;
            case 'output_run':
                $element_uri = $qualityAnnotation->getOutputRun()->getUri();
                break;
        }
        
        $query = 
        "   MODIFY <".$this->driver->getDefaultGraph('qualitydimension-annotation')."> 
            DELETE 
            { 
                <".$qualityAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityAnnotation->getUri()."> oa:hasTarget ?element.
                <".$qualityAnnotation->getUri()."> w2share:hasValue ?value.    
                <".$qualityAnnotation->getUri()."> w2share:hasQualityDimension ?qualityDimension.
                ?qualityDimension <w2share:qdName> ?qdName.
            }
            INSERT        
            { 
                <".$qualityAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityAnnotation->getUri()."> oa:hasTarget <".$element_uri.">.
                <".$qualityAnnotation->getUri()."> w2share:hasValue ?value.    
                <".$qualityAnnotation->getUri()."> w2share:hasQualityDimension ?qualityDimension.
                ?qualityDimension <w2share:qdName> ?qdName.
            }
            WHERE 
            { 
                <".$qualityAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityAnnotation->getUri()."> oa:hasTarget ?element.
                <".$qualityAnnotation->getUri()."> w2share:hasValue ?value.    
                <".$qualityAnnotation->getUri()."> w2share:hasQualityDimension ?qualityDimension.
                ?qualityDimension <w2share:qdName> ?qdName.
            }";
        return $this->driver->getResults($query);
        
    }
    //TO-DO
    public function deleteQualityAnnotation(\AppBundle\Entity\QualityAnnotation $qualityAnnotation, $type)
    { 
        $element_uri = "";
        
        switch ($type)
        {
            case 'workflow':
                $element_uri = $qualityAnnotation->getWorkflow()->getUri();
                break;
            case 'process_run':
                $element_uri = $qualityAnnotation->getProcessRun()->getUri();
                break;
            case 'output_run':
                $element_uri = $qualityAnnotation->getOutputRun()->getUri();
                break;
        }
        
       $query = 
        "DELETE data FROM <".$this->driver->getDefaultGraph('qualitydimension-annotation')."> 
            {
                <".$qualityAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityAnnotation->getUri()."> oa:hasTarget <".$element_uri.">.
                <".$qualityAnnotation->getUri()."> w2share:hasValue '".$qualityAnnotation->getValue()."'.    
                <".$qualityAnnotation->getUri()."> w2share:hasQualityDimension <".$qualityAnnotation->getQualityDimension().">.
                <".$qualityAnnotation->getQualityDimension()."> <w2share:qdName> '".$qualityAnnotation->getQualityDimension()->getName()."'.
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
