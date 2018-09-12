<?php
namespace AppBundle\Dao;

use AppBundle\AppBundle;
use http\QueryString;

/**
 * Description of the Research Object model
 *
 * @author lucas
 */
class WRODAO
{
    private $driver;
        
    private $container;
    
    public function __construct($driver, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
        $this->driver = $driver;
    }                  
    
    public function findAll()
    {
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a ro:ResearchObject.
            ?uri dc:created ?createdAt.
            OPTIONAL { ?uri dc:creator ?creator.             
            ?creator <foaf:name> ?name. }
            OPTIONAL { ?uri dc:description ?description. }
            OPTIONAL { ?uri dc:title ?title. }
        }";
       
        $wros = $this->driver->getResults($query);
        
        $wro_array = array();
        
        for ($i = 0; $i < count($wros); $i++)
        {   
            $wro = new \AppBundle\Entity\WRO();            
            $wro->setUri($wros[$i]['uri']['value']);
            $wro->setCreatedAt($wros[$i]['createdAt']['value']); 
            
            if (array_key_exists('creator', $wros[$i]))
            {
                $creator = new \AppBundle\Entity\Person();
                $creator->setUri($wros[$i]['creator']['value']);
                $creator->setName($wros[$i]['name']['value']);

                $wro->setCreator($creator);     
            }
            if (array_key_exists('description', $wros[$i]))
            {
                $wro->setDescription($wros[$i]['description']['value']);
            }
            
            if (array_key_exists('title', $wros[$i]))
            {
                $wro->setTitle($wros[$i]['title']['value']);
            }
            
            $wro_array[] = $wro;
        } 
        
        return $wro_array;
    }
    
    public function findAllResourcesByWRO(\AppBundle\Entity\WRO $wro)
    {
        $query = 
        "SELECT DISTINCT ?resource ?type ?description ?title WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$wro->getUri()."> a ro:ResearchObject, wf4ever:WorkflowResearchObject.
                <".$wro->getUri()."> ore:aggregates ?resource.
                ?resource a ro:Resource, ?type.
                FILTER ( ! regex(?type, \"Resource\", \"i\" ))
                OPTIONAL { ?resource dc:description ?description. }
                OPTIONAL { ?resource dc:title ?title. }
            }
        }";
       
        $result_array = $this->driver->getResults($query);
        
        $results_array = array();
        
        for ($i = 0; $i < count($result_array); $i++)
        {   
            $resource = new \AppBundle\Entity\WROResource();            
            $resource->setUri($result_array[$i]['resource']['value']);
            $resource->setType($result_array[$i]['type']['value']); 
            
            if (array_key_exists('description', $result_array[$i]))
            {
                $resource->setDescription($result_array[$i]['description']['value']);
            }
            if (array_key_exists('title', $result_array[$i]))
            {
                $resource->setTitle($result_array[$i]['title']['value']);
            }
            
            $results_array[] = $resource;
        } 
        
        return $results_array;
    }

    public function findAllQEDByWRO(\AppBundle\Entity\WRO $wro)
    {
        
        $query =
            "SELECT DISTINCT * WHERE        
        { 
          
          
                   <".$wro->getUri()."> <w2share:hasQualityEvidenceData> ?qed.
                   ?qed a <w2share:QualityEvidenceData> ;
                    <w2share:contains> ?resource ;
                    <w2share:hasDataType> ?qdt ;
                <dc:date> ?date;
                <dc:creator> ?creator.
                ?creator <foaf:name> ?name.
            
        }";

        $result_array = $this->driver->getResults($query);

        $results_array = array();


        for ($i = 0; $i < count($result_array); $i++)
        {
            $qed = new \AppBundle\Entity\QualityEvidenceData();
            $qed->setUri($result_array[$i]['qed']['value']);
            $qed->setCreatedAtTime($result_array[$i]['date']['value']);

            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[$i]['creator']['value']);
            $creator->setName($result_array[$i]['name']['value']);

            $qed->setCreator($creator);
            $qed->setResource($this->findResource($result_array[$i]['resource']['value']));
            $qed->setQualityDataType($this->findOneQDT($result_array[$i]['qdt']['value']));

            $results_array[] = $qed;
        }

       
        return $results_array;
    }

    public function findQED($uri)
    {
        $query =
            "SELECT DISTINCT * WHERE        
        {  
            { 
               ?wro <w2share:hasQualityEvidenceData>  <".$uri.">.
               <".$uri.">  a <w2share:QualityEvidenceData> ;
                <w2share:contains> ?resource ;
                <w2share:hasDataType> ?qdt ;
                <dc:date> ?date;
                <dc:creator> ?creator.
                ?creator <foaf:name> ?name.
              
                
            }
        }";


        $result_array = $this->driver->getResults($query);
        if (count($result_array) > 0){
            $qed = new \AppBundle\Entity\QualityEvidenceData();
            $qed->setCreatedAtTime($result_array[0]['date']['value']);

            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[0]['creator']['value']);
            $creator->setName($result_array[0]['name']['value']);
            $qed->setCreator($creator);

            $qed->setResource($this->findResource($result_array[0]['resource']['value']));
            $qed->setQualityDataType($this->findOneQDT($result_array[0]['qdt']['value']));
            $qed->setWro($this->findWRO($result_array[0]['wro']['value']));

            return $qed;
            }
    }



    public function findAllDimensionsByQDT(\AppBundle\Entity\QualityDataType $qdt)
    {
        $query =
            "SELECT DISTINCT * WHERE        
        { 
            <".$qdt->getUri()."> <w2share:describesQualityDimension> ?dimension .
            ?dimension a <w2share:QualityDimension> ;
            <w2share:qdName> ?name ;
            <w2share:valueType> ?valueType ;
            <rdfs:description> ?description ;
            <dc:creator> ?creator .
            ?creator <foaf:name> ?creator_name .
        }";

        $quality_dimension_array = array();
        $quality_dimensions = $this->driver->getResults($query);
        for ($i = 0; $i < count($quality_dimensions); $i++)
        {
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_dimensions[$i]['dimension']['value']);
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


    public function findOneQDT($uri)
    {
        $query =
            "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydatatype')."> 
            { 
                <".$uri."> a <w2share:QualityDataType>.
                <".$uri."> <w2share:qdtName> ?name.
                <".$uri."> <w2share:isMandatory> ?bool.
            }
        }";

        $qdt = $this->driver->getResults($query);

        $qualityDataType = new \AppBundle\Entity\QualityDataType();
        try {
            $qualityDataType->setUri($uri);
            $qualityDataType->setName($qdt[0]['name']['value']);
            $qualityDataType->setIsMandatory($qdt[0]['bool']['value']);
            $qualitydimensions = $this->findAllDimensionsByQDT($qualityDataType);
            $qualityDataType->setQualityDimensions($qualitydimensions);
        } catch (\Symfony\Component\Debug\Exception\ContextErrorException $ex) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Data Type not found!");
        }

        return $qualityDataType;
    }

    public function findUnusedQDTByWRO(\AppBundle\Entity\WRO $wro){
     $query = "
        SELECT * WHERE {
            ?qdt a <w2share:QualityDataType>;
            <w2share:qdtName> ?name;
            <w2share:isMandatory> ?bool.
            MINUS {
                <".$wro->getUri()."> <w2share:hasQualityEvidenceData> ?qed.
                ?qed a <w2share:QualityEvidenceData> ;
                <w2share:hasDataType> ?qdt.

            }
        }

    ";
        $qdt_array = array();
        $qdts = $this->driver->getResults($query);

        for ($i = 0; $i < count($qdts); $i++)
        {
            $qdt = new \AppBundle\Entity\QualityDataType();
            $qdt->setUri($qdts[$i]['qdt']['value']);
            $qdt->setName($qdts[$i]['name']['value']);
            $qdt->setIsMandatory($qdts[$i]['bool']['value']);
            $qdt->setQualityDimensions($this->findAllDimensionsByQDT($qdt));
            $qdt_array[$qdt->getUri()] = $qdt;
        }

        return $qdt_array;
    }

    public function findUnusedResourcesByWRO( \AppBundle\Entity\WRO $wro){
        $query = "
        SELECT * WHERE {
            <".$wro->getUri()."> ore:aggregates ?resource.
            ?resource a ro:Resource
            MINUS {
                <".$wro->getUri()."> <w2share:hasQualityEvidenceData> ?qed.
                ?qed a <w2share:QualityEvidenceData> ;
                <w2share:contains> ?resource.
            
            }
        }";

        $resource_array = array();
        $results = $this->driver->getResults($query);

        for ($i = 0; $i < count($results); $i++) {
            $resource = $this->findResource($results[$i]['resource']['value']);
            $resource_array[$resource->getUri()] = $resource;
        }
        return $resource_array;

    }

    public function findResource($uri)
    {
        $query = 
        "SELECT * WHERE 
            { 
                ?wro a ro:ResearchObject, wf4ever:WorkflowResearchObject.
                ?wro ore:aggregates <".$uri.">.
                <".$uri."> a ro:Resource, ?type.
                FILTER ( ! regex(?type, \"Resource\", \"i\" ))
                OPTIONAL { <".$uri."> dc:description ?description. }
                OPTIONAL { <".$uri."> dc:title ?title. }
                OPTIONAL {  ?conversion <w2share:hasWorkflowResearchObject> ?wro.
                            ?conversion <w2share:hash> ?hash 
                }
        }";
       
        $result_array = $this->driver->getResults($query);
                
        if (count($result_array) > 0)
        {   
            $resource = new \AppBundle\Entity\WROResource();            
            $resource->setUri($uri);
            $resource->setType($result_array[0]['type']['value']); 
            
            if (array_key_exists('description', $result_array[0]))
            {
                $resource->setDescription($result_array[0]['description']['value']);
            }
            if (array_key_exists('title', $result_array[0]))
            {
                $resource->setTitle($result_array[0]['title']['value']);
            }                        
            
            $wro = new \AppBundle\Entity\WRO();
            $wro->setUri($result_array[0]['wro']['value']);
            
            if (array_key_exists('hash', $result_array[0]))
            {
                $wro->setHash($result_array[0]['hash']['value']);
            }
            
            $resource->setWro($wro);
            
            return $resource;
        } 
        
        return null;
    }

    public function updateQED($qed_uri, \AppBundle\Entity\QualityEvidenceData $qed, \AppBundle\Entity\Person $user){
        $this->deleteQED($qed_uri);
        $this->addQED($qed, $user);
    }

    public function updateResource(\AppBundle\Entity\WROResource $resource)
    {
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph('wro').">
        DELETE
        {
            <".$resource->getUri()."> a ro:Resource, ?type.
            <".$resource->getUri()."> dc:description ?description.
            <".$resource->getUri()."> dc:title ?title.
        }
        INSERT
        {             
            <".$resource->getUri()."> a ro:Resource, <".$resource->getType().">.
            <".$resource->getUri()."> dc:description '".$resource->getDescription()."'.
            <".$resource->getUri()."> dc:title '".$resource->getTitle()."'.
        }
        WHERE
        {
            <".$resource->getUri()."> a ro:Resource, ?type.
            OPTIONAL { ?resource dc:description ?description. }
            OPTIONAL { ?resource dc:title ?title. }
        }";
       
        $this->driver->getResults($query);
    }
    
    public function addResource(\AppBundle\Entity\WROResource $resource)
    {
        $uri = \AppBundle\Utils\Utils::convertNameToUri('wro', '/'.$resource->getWro()->getHash().'/'.$resource->getFilename());
        $resource->setUri($uri);        
        
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$resource->getWro()->getUri()."> ore:aggregates <".$resource->getUri().">.
                <".$resource->getUri()."> a ro:Resource, <".$resource->getType().">.
                <".$resource->getUri()."> dc:description '".$resource->getDescription()."'.
                <".$resource->getUri()."> dc:title '".$resource->getTitle()."'.
            }
        }";
       
        $this->driver->getResults($query);
        
        return $resource;
    }

    public function addQED(\AppBundle\Entity\QualityEvidenceData $qed, $user)
    {
        $uri = \AppBundle\Utils\Utils::convertNameToUri('wro', '/'.$qed->getWro()->getHash().'/'.$qed->getResource()->getFilename().'/'.$qed->getQualityDataType()->getName());
        $qed->setUri($uri);

        $query =
            "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$qed->getWro()->getUri()."> <w2share:hasQualityEvidenceData> <".$qed->getUri().">.
                <".$qed->getUri()."> a <w2share:QualityEvidenceData> ;
                <w2share:contains> <".$qed->getResource()->getUri()."> ;
                <w2share:hasDataType> <".$qed->getQualityDataType()->getUri()."> ;
                <dc:date> '".$qed->getCreatedAtTime()->format('Y-m-d')."T".$qed->getCreatedAtTime()->format('H:i:s')."';
                <dc:creator> <".$user->getUri().">.
            }
        }";

        $this->driver->getResults($query);

        return $qed;
    }
    
    public function updateWRO(\AppBundle\Entity\WRO $wro)
    {        
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph('wro').">
        DELETE
        {
            <".$wro->getUri()."> dc:title ?title.
            <".$wro->getUri()."> dc:description ?description.         
        }
        INSERT
        {
            <".$wro->getUri()."> dc:title '".$wro->getTitle()."'.
            <".$wro->getUri()."> dc:description '".$wro->getDescription()."'.          
        }
        WHERE
        {
            <".$wro->getUri()."> a ro:ResearchObject, wf4ever:WorkflowResearchObject.
            OPTIONAL { <".$wro->getUri()."> dc:title ?title. }
            OPTIONAL { <".$wro->getUri()."> dc:description ?description. }    
        }";
       
        $this->driver->getResults($query);
    }
    
    public function findWRO($uri)
    {
        $query = 
        "SELECT * WHERE        
        { 
            <".$uri."> a ro:ResearchObject, wf4ever:WorkflowResearchObject.
            <".$uri."> dc:created ?createdAt.
            OPTIONAL {
                <".$uri."> dc:creator ?creator.            
                ?creator <foaf:name> ?name. }
            OPTIONAL { <".$uri."> dc:title ?title. }
            OPTIONAL { <".$uri."> dc:description ?description. }
            OPTIONAL {  ?conversion <w2share:hasWorkflowResearchObject> <".$uri.">.
                        ?conversion <w2share:hash> ?hash 
                    }
        }";
       
        $result_array = $this->driver->getResults($query);
           
        if (count($result_array) > 0)
        {
            $wro = new \AppBundle\Entity\WRO();            
            $wro->setUri($uri);
            $wro->setCreatedAt($result_array[0]['createdAt']['value']); 
            
            if (array_key_exists('creator', $result_array[0]))
            {
                $creator = new \AppBundle\Entity\Person();
                $creator->setUri($result_array[0]['creator']['value']);
                $creator->setName($result_array[0]['name']['value']);

                $wro->setCreator($creator);
            }
            
            if (array_key_exists('description', $result_array[0]))
            {
                $wro->setDescription($result_array[0]['description']['value']);
            }
            
            if (array_key_exists('title', $result_array[0]))
            {
                $wro->setTitle($result_array[0]['title']['value']);
            }
            
            if (array_key_exists('conversion', $result_array[0]))
            {
                $conversion = new \AppBundle\Entity\ScriptConverter();
                $conversion->setUri($result_array[0]['conversion']['value']);
                $conversion->setHash($result_array[0]['hash']['value']);
                $wro->setScriptConversion($conversion);
                $wro->setHash($result_array[0]['hash']['value']);
            }
            
            $resources = $this->findAllResourcesByWRO($wro);
            $wro->setResources($resources);


            $qed = $this->findAllQEDByWRO($wro);
            $wro->setQualityEvidenceData($qed);

            return $wro;
        }
        
        return null;
    }

    public function saveWROScriptConversion(\AppBundle\Entity\WRO $wro) 
    {      
        $query = 
        "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('scriptconverter')."> 
            { 
                <".$wro->getScriptConversion()->getUri()."> <w2share:hasWorkflowResearchObject> <".$wro->getUri().">. 
            }
        }"; 
        $this->driver->getResults($query);       
    }                                
            
    public function clearGraph()
    {               
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('wro').">";        
        return $this->driver->getResults($query);                  
    }        
    
    public function saveWRO(\AppBundle\Entity\WRO $wro)
    {
        // ore:aggregates <script.".$wro->getScriptConversion()->getScriptExtension().">, <abstract-workflow.svg> ;
        // dc:creator <".$wro->getCreator()->getUri().">.     
        $query = "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$wro->getUri()."> a ro:ResearchObject, ore:Aggregation, wf4ever:WorkflowResearchObject ;                 
                dc:created '".$wro->getCreatedAt()->format(\DateTime::ISO8601)."' .                          
            }
        }"; 

        $this->driver->getResults($query); 
    }
    
    public function saveWROResources(\AppBundle\Entity\WRO $wro)
    {
        $query = "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            {\n";
        
            foreach($wro->getResources() as $resource)
            {
                $uri = \AppBundle\Utils\Utils::convertNameToUri('wro', $wro->getHash()).'/'.$resource->getFilename();
                $resource->setUri($uri); 
        
                $query .= "<".$wro->getUri()."> ore:aggregates <".$resource->getUri().">.\n"; 
                $query .= "<".$resource->getUri()."> dc:description '".$resource->getDescription()."'.\n";
                $query .= "<".$resource->getUri()."> dc:title '".$resource->getTitle()."'.\n";
                $query .= "<".$resource->getUri()."> a ro:Resource, ".$resource->getType().".\n";
            }
            
        $query .= "   }
        }";                

        $this->driver->getResults($query, true);
    }
    
    public function addWorkflowWRO(\AppBundle\Entity\WRO $wro)
    {
        $query = "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$wro->getUri()."> ore:aggregates <a_workflow.t2flow> .
                <a_workflow.t2flow> a ro:Resource .
            }
        }"; 

        return $this->driver->getResults($query);         
    } 
    
    /**
     * Delete triples related to a workflow URI
     * @param type Workflow
     */
    public function deleteWRO(\AppBundle\Entity\WRO $wro)
    {
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('wro')."> {
                <".$wro->getUri()."> ?property ?object.                
            }
            WHERE {
                <".$wro->getUri()."> ?property ?object.  
            }
            ";  
        $this->driver->getResults($query);
        
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('scriptconverter')."> {
                ?subject ?property <".$wro->getUri().">.                
            }
            WHERE
            {
               ?subject ?property  <".$wro->getUri().">.  
            }
            ";  
        $this->driver->getResults($query);               
    }
    
    public function resetWROScriptConversion()
    {
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('scriptconverter')."> {
                ?conversion <w2share:hasWorkflowResearchObject> ?wro.                
            }
            WHERE {
                ?conversion <w2share:hasWorkflowResearchObject> ?wro.     
            }
            ";  
        $this->driver->getResults($query);               
    }
        
    public function deleteWROResource($uri)
    {
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('wro')."> {
                <".$uri."> ?property ?object.                
            }
            WHERE {
                <".$uri."> ?property ?object.  
            }
            ";  
        $this->driver->getResults($query);
        
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('wro')."> {
                ?subject ?property <".$uri.">.                
            }
            WHERE {
                ?subject ?property <".$uri.">.  
            }
            "; 
 
        $this->driver->getResults($query);               
    }

    public function deleteQED($uri)
    {
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('wro')."> {
                <".$uri."> ?property ?object.                
            }
            WHERE {
                <".$uri."> ?property ?object.  
            }
            ";
        $this->driver->getResults($query);

        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph('wro')."> {
                ?subject ?property <".$uri.">.                
            }
            WHERE {
                ?subject ?property <".$uri.">.  
            }
            ";

        $this->driver->getResults($query);
    }



}
