<?php
namespace AppBundle\Model;
use AppBundle\Utils\Utils;

/**
 * Description of Provenance
 *
 * @author lucas
 */
class QualityAnnotation
{
    private $driver;    
    
    public function __construct($driver)
    {
        $this->driver = $driver;
    }                
        
    public function insertQualityAnnotation($element_uri, $type, \AppBundle\Entity\QualityDimension $qualityDimension, $value, $user)
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
            
            $qualityAnnotation->setType($type);
            $qualityAnnotation->setElementUri($quality_annotation[0]['element']['value']);

            return $qualityAnnotation;  
        } 
        
        return null;
    }
    
    public function findQualityAnnotationsByElement($uri, $type)
    {
        $query = 
        "SELECT DISTINCT ?uri ?qualityDimension ?qdValue ?qdName 
                         ?creator ?metric_uri ?result ?metric ?description ?creator_name 
         WHERE        
        { 
            ?uri a w2share:QualityAnnotation.
            ?uri oa:hasTarget <".$uri.">.
            ?uri w2share:hasValue ?qdValue.
            ?uri w2share:hasQualityDimension ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            ?uri oa:annotatedBy ?creator.
            ?creator <foaf:name> ?creator_name.
            OPTIONAL {  ?uri oa:hasBody ?body.
                        ?body w2share:hasQualityMetric ?metric_uri. 
                        ?body w2share:hasQualityMetricResult ?result.
                        ?metric_uri <w2share:metric> ?metric.
                        ?metric_uri <rdfs:description> ?description.
                      }
        }"; 
        
        $quality_annotation_array = array();
        $quality_annotations = $this->driver->getResults($query);   
        
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        $qualityAnnotation->setType($type);
        $qualityAnnotation->setElementUri($uri);
                
        for ($i = 0; $i < count($quality_annotations); $i++)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
            $qualityAnnotation->setUri($quality_annotations[$i]['uri']['value']);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_annotations[$i]['qualityDimension']['value']);
            $qualityDimension->setName($quality_annotations[$i]['qdName']['value']);
            
            $qualityAnnotation->setQualityDimension($qualityDimension);
            $qualityAnnotation->setValue($quality_annotations[$i]['qdValue']['value']);
            
            if ( array_key_exists('metric_uri', $quality_annotations[$i]) )
            {
                $qualityMetric = new \AppBundle\Entity\QualityMetric();
                $qualityMetric->setUri($quality_annotations[$i]['metric_uri']['value']);
                $qualityMetric->setMetric($quality_annotations[$i]['metric']['value']);
                $qualityMetric->setDescription($quality_annotations[$i]['description']['value']);

                $qualityMetricAnnotation = new \AppBundle\Entity\QualityMetricAnnotation();
                //TODO: Por agora, a quality metric annotation nao tem URI.
                //$qualityMetricAnnotation->setUri(?);
                $qualityMetricAnnotation->setQualityMetric($qualityMetric);
                $qualityMetricAnnotation->setResult($quality_annotations[$i]['result']['value']);

                $qualityAnnotation->setQualityMetricAnnotation($qualityMetricAnnotation);
            }
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($quality_annotations[$i]['creator']['value']);
            $creator->setName($quality_annotations[$i]['creator_name']['value']);

            $qualityAnnotation->setCreator($creator);
            
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
    
    public function updateQualityAnnotation(\AppBundle\Entity\QualityAnnotation $qualityAnnotation) 
    {        
        $element_uri = $qualityAnnotation->getElementUri();
        
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
                <".$qualityAnnotation->getUri()."> w2share:hasValue '".$qualityAnnotation->getValue()."'.   
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
    
    public function deleteQualityAnnotation(\AppBundle\Entity\QualityAnnotation $qualityAnnotation)
    { 
       $element_uri = $qualityAnnotation->getElementUri();
        
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
    
    public function insertQualityMetricAnnotation($uri, \AppBundle\Entity\QualityMetric $qualityMetric, $result, $user)
    {
        $now = new \DateTime();
        //TODO: uri
            //Inicialmente nao iremos criar uma URI para a anotacao de QualityMetric
        
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitymetric-annotation')."> 
            { 
                <".$uri."> a w2share:QualityAnnotation;
                oa:hasBody [ 
                             w2share:hasQualityMetric <".$qualityMetric->getUri()."> ;
                             w2share:hasQualityMetricResult '".$result."' ].
                <".$uri."> oa:annotatedAt \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\".
                <".$uri."> oa:annotatedBy <".$user->getUri().">. 
            }
        }";
        
        return $this->driver->getResults($query);    
    }
    
    public function findQualityMetricAnnotation($uri)
    {
        $query = 
        "SELECT DISTINCT ?qualityDimension ?qdName ?body ?metric_uri ?result ?metric 
            ?description ?creator ?creator_name 
         WHERE        
        { 
            <".$uri."> a w2share:QualityAnnotation.
            <".$uri."> w2share:hasQualityDimension ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            <".$uri."> oa:hasBody ?body.
                ?body w2share:hasQualityMetric ?metric_uri. 
                ?body w2share:hasQualityMetricResult ?result.
                ?metric_uri <w2share:metric> ?metric.
                ?metric_uri <rdfs:description> ?description. 
                ?metric_uri <dc:creator> ?creator.
                ?creator <foaf:name> ?creator_name.
        }";   
        
        $quality_metric_annotation = $this->driver->getResults($query);
        
        $qualityMetricAnnotation = new \AppBundle\Entity\QualityMetricAnnotation();
        $qualityMetric = new \AppBundle\Entity\QualityMetric();
        
        $qualityMetricAnnotation->setUri($uri);
        
        if (count($quality_metric_annotation) > 0) 
        {
            $qualityMetric = new \AppBundle\Entity\QualityMetric();
            $qualityMetric->setUri($quality_metric_annotation[0]['metric_uri']['value']);
            $qualityMetric->setMetric($quality_metric_annotation[0]['metric']['value']);
            $qualityMetric->setDescription($quality_metric_annotation[0]['description']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($quality_metric_annotation[0]['creator']['value']);
            $creator->setName($quality_metric_annotation[0]['creator_name']['value']);
            
            $qualityMetric->setCreator($creator);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_metric_annotation[0]['qualityDimension']['value']);
            $qualityDimension->setName($quality_metric_annotation[0]['qdName']['value']);
            
            $qualityMetric->setQualityDimension($qualityDimension);
            
            $qualityMetricAnnotation->setQualityMetric($qualityMetric);
            $qualityMetricAnnotation->setResult($quality_metric_annotation[0]['result']['value']);
                    
            
            return $qualityMetricAnnotation;
        } 
        
        return null;
    }
    
    public function updateQualityMetricAnnotation(\AppBundle\Entity\QualityMetricAnnotation $qualityMetricAnnotation, $user) 
    {   
        $now = new \DateTime();
        $query = 
        "   MODIFY <".$this->driver->getDefaultGraph('qualitymetric-annotation')."> 
            DELETE 
            { 
                <".$qualityMetricAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityMetricAnnotation->getUri()."> oa:hasBody ?body.
                    ?body w2share:hasQualityMetric <".$qualityMetricAnnotation->getQualityMetric()->getUri().">. 
                    ?body w2share:hasQualityMetricResult ?result.
            }
            INSERT        
            { 
                <".$qualityMetricAnnotation->getUri()."> a w2share:QualityAnnotation;
                oa:hasBody [ 
                             w2share:hasQualityMetric <".$qualityMetricAnnotation->getQualityMetric()->getUri()."> ;
                             w2share:hasQualityMetricResult '".$qualityMetricAnnotation->getResult()."' ].   
                <".$qualityMetricAnnotation->getUri()."> oa:annotatedAt \"".$now->format('Y-m-d')."T".$now->format('H:i:s')."Z\".
                <".$qualityMetricAnnotation->getUri()."> oa:annotatedBy <".$user->getUri().">. 
            }
            WHERE 
            { 
                <".$qualityMetricAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityMetricAnnotation->getUri()."> oa:hasBody ?body.
                    ?body w2share:hasQualityMetric <".$qualityMetricAnnotation->getQualityMetric()->getUri().">. 
                    ?body w2share:hasQualityMetricResult ?result.
            }";
        
        return $this->driver->getResults($query);  
    }
    
    public function deleteQualityMetricAnnotation(\AppBundle\Entity\QualityMetricAnnotation $qualityMetricAnnotation)
    {
       $query = 
        "DELETE WHERE {
            GRAPH <".$this->driver->getDefaultGraph('qualitymetric-annotation')."> 
            {
                <".$qualityMetricAnnotation->getUri()."> a w2share:QualityAnnotation.
                <".$qualityMetricAnnotation->getUri()."> oa:hasBody ?body.
                    ?body w2share:hasQualityMetric ?metric. 
                    ?body w2share:hasQualityMetricResult ?result.
            }
        }";
        
        $this->driver->getResults($query);
    }
    
}