<?php
namespace AppBundle\Model;

/**
 * Description of Research Object model
 *
 * @author lucas
 */
class ResearchObject
{
    private $driver;
        
    public function __construct($driver)
    {
        $this->driver = $driver;
    }                
    
    public function findAll()
    {
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a w2share:QualityAnnotation.
            ?uri oa:hasTarget ?element.
            ?uri w2share:hasValue ?value.
            ?uri w2share:hasQualityDimension ?qualityDimension.
            ?qualityDimension <w2share:qdName> ?qdName.
            
        }";
       
        $quality_annotation = $this->driver->getResults($query);
        
        $ro_array = array();
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        if (count($quality_annotation) > 0)
        {   
            $qualityAnnotation->setUri($uri);
            
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_annotation[0]['qualityDimension']['value']);
            $qualityDimension->setName($quality_annotation[0]['qdName']['value']);
            
            $qualityAnnotation->setQualityDimension($qualityDimension);
            
            $qualityAnnotation->setValue($quality_annotation[0]['value']['value']);                        
            
            return $qualityAnnotation;  
        } 
        
        return $ro_array;
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
