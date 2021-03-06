<?php
namespace AppBundle\Dao;

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
}
