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
            ?uri dc:creator ?creator. 
            ?creator <foaf:name> ?name.
        }";
       
        $wros = $this->driver->getResults($query);
        
        $wro_array = array();
        
        for ($i = 0; $i < count($wros); $i++)
        {   
            $wro = new \AppBundle\Entity\WRO();            
            $wro->setUri($wros[$i]['uri']['value']);
            $wro->setCreatedAt($wros[$i]['createdAt']['value']); 
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($wros[$i]['creator']['value']);
            $creator->setName($wros[$i]['name']['value']);
            
            $wro->setCreator($creator);                        
            
            $wro_array[] = $wro;
        } 
        
        return $wro_array;
    }
    
    public function findAllResourcesByWRO(\AppBundle\Entity\WRO $wro)
    {
        $query = 
        "SELECT * WHERE        
        { 
            <".$wro->getUri()."> a ro:ResearchObject, wf4ever:WorkflowResearchObject.
            <".$wro->getUri()."> ore:aggregates ?resource.
            ?resource a ?type. 
        }";
       
        $result_array = $this->driver->getResults($query);
        
        $results_array = array();
        
        for ($i = 0; $i < count($result_array); $i++)
        {   
            $resource = new \AppBundle\Entity\WROResource();            
            $resource->setUri($result_array[$i]['resource']['value']);
            $resource->setType($result_array[$i]['type']['value']);            
            
            $results_array[] = $resource;
        } 
        
        return $results_array;
    }
    
    public function findWRO($uri)
    {
        $query = 
        "SELECT * WHERE        
        { 
            <".$uri."> a ro:ResearchObject, wf4ever:WorkflowResearchObject.
            <".$uri."> dc:created ?createdAt.
            <".$uri."> dc:creator ?creator. 
            ?creator <foaf:name> ?name.
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
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[0]['creator']['value']);
            $creator->setName($result_array[0]['name']['value']);

            $wro->setCreator($creator); 
            
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
    
    private function loadIntoDB(\AppBundle\Entity\WRO $wro, $file_path)
    {        
        $env = $this->container->get('kernel')->getEnvironment();
        
        $path_url = '';
        if ($env == 'dev')
        {
            $path_url = "http://"
                . $this->container->get('request')->getHost();
        }
        $path_url .= $this->container->get('templating.helper.assets')
                ->getUrl("/uploads/documents/wro/".$wro->getHash()."/".$file_path, null, true, true);
        
        $query = "LOAD <".$path_url."> INTO graph <".$this->driver->getDefaultGraph('wro').">";
        $this->driver->getResults($query);
    }
            
    public function clearGraph()
    {               
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('wro').">";        
        return $this->driver->getResults($query);                  
    }        
    
    public function saveWRO(\AppBundle\Entity\WRO $wro)
    {
        $query = "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$wro->getUri()."> a ro:ResearchObject, ore:Aggregation, wf4ever:WorkflowResearchObject ; 
                ore:aggregates <script.".$wro->getScriptConversion()->getScriptExtension().">, <abstract-workflow.svg> ;
                dc:created '".$wro->getCreatedAt()->format(\DateTime::ISO8601)."' ;
                dc:creator <".$wro->getCreator()->getUri().">.
                
                <script.".$wro->getScriptConversion()->getScriptExtension()."> a ro:Resource, wf4ever:Script.
                <abstract-workflow.svg> a ro:Resource, wf4ever:Image.
            }
        }"; 

        $this->driver->getResults($query); 
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
}