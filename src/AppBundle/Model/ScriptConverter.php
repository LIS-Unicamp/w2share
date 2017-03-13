<?php
namespace AppBundle\Model;

/**
 * Description of ScriptConverter
 *
 * @author lucas
 */
class ScriptConverter
{    
    private $driver;
        
    private $container;
    
    public function __construct($driver, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
        $this->driver = $driver;
    }       
    
    public function insertScriptConversion(\AppBundle\Entity\ScriptConverter $converter, $user) 
    { 
        $uri = \AppBundle\Utils\Utils::convertNameToUri("Script Converter", $converter->getHash());
        $converter->setUri($uri);
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('scriptconverter')."> 
            { 
                <".$uri."> a <w2share:ScriptConversion>.
                <".$uri."> <w2share:hash> '".$converter->getHash()."'.
                <".$uri."> <w2share:scriptLanguage> '".$converter->getScriptLanguage()."'.
                <".$uri."> <w2share:createdAt> '".$converter->getCreatedAt()->format(\DateTime::ISO8601)."'.
                <".$uri."> <w2share:updatedAt> '".$converter->getUpdatedAt()->format(\DateTime::ISO8601)."'.
                <".$uri."> <dc:creator> <".$user->getUri().">. 
            }
        }"; 

        return $this->driver->getResults($query);        
    }
    
    public function findOneScriptConversionByURI($uri) 
    { 
        $query = 
        "SELECT * WHERE        
        { 
            <".$uri."> a <w2share:ScriptConversion>.
            <".$uri."> <w2share:hash> ?hash.
            <".$uri."> <w2share:scriptLanguage> ?scriptLanguage.
            <".$uri."> <w2share:createdAt> ?createdAt.
            <".$uri."> <w2share:updatedAt> ?updatedAt.
            <".$uri."> <dc:creator> ?creator. 
            ?creator <foaf:name> ?name.            
        }";   
        
        $result_array = $this->driver->getResults($query);
        
        if (count($result_array) > 0)
        {
            $converter = new \AppBundle\Entity\ScriptConverter();
            $converter->setUri($uri);
            $converter->setHash($result_array[0]['hash']['value']);
            $converter->setCreatedAt(new \Datetime($result_array[0]['createdAt']['value']));
            $converter->setUpdatedAt(new \Datetime($result_array[0]['updatedAt']['value']));
            $converter->setScriptLanguage($result_array[0]['scriptLanguage']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[0]['creator']['value']);
            $creator->setName($result_array[0]['name']['value']);
            $converter->setCreator($creator);
                
            return $converter;    
        }
        return null;
    }
    
    public function findOneScriptConversionByHash($hash) 
    { 
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a <w2share:ScriptConversion>.
            ?uri <w2share:hash> '".$hash."'.
            ?uri <w2share:scriptLanguage> ?scriptLanguage.
            ?uri <w2share:createdAt> ?createdAt.
            ?uri <w2share:updatedAt> ?updatedAt.
            ?uri <dc:creator> ?creator. 
            ?creator <foaf:name> ?name.
        }";   
        
        $result_array = $this->driver->getResults($query);
        
        if (count($result_array) > 0)
        {
            $converter = new \AppBundle\Entity\ScriptConverter();
            $converter->setUri($result_array[0]['uri']['value']);
            $converter->setHash($hash);
            $converter->setCreatedAt(new \Datetime($result_array[0]['createdAt']['value']));
            $converter->setUpdatedAt(new \Datetime($result_array[0]['updatedAt']['value']));
            $converter->setScriptLanguage($result_array[0]['scriptLanguage']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[0]['creator']['value']);
            $creator->setName($result_array[0]['name']['value']);
            $converter->setCreator($creator);
                
            return $converter;    
        }
        return null;
    }
    
    public function findScriptConversions() 
    { 
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a <w2share:ScriptConversion>.
            ?uri <w2share:hash> ?hash.
            ?uri <w2share:scriptLanguage> ?scriptLanguage.
            ?uri <w2share:createdAt> ?createdAt.
            ?uri <w2share:updatedAt> ?updatedAt.
            OPTIONAL { ?uri <w2share:hasWorkflow> ?workflow. }
            OPTIONAL { ?uri <w2share:hasWorkflowResearchObject> ?wro. }
            ?uri <dc:creator> ?creator. 
            ?creator <foaf:name> ?name.
        }";   
        
        $result_array = $this->driver->getResults($query);
        $conversion = array();
        
        for ($i=0; $i < count($result_array); $i++)
        {
            $converter = new \AppBundle\Entity\ScriptConverter();
            $converter->setHash($result_array[$i]['hash']['value']);
            $converter->setUri($result_array[$i]['uri']['value']);
            $converter->setCreatedAt(new \Datetime($result_array[$i]['createdAt']['value']));
            $converter->setUpdatedAt(new \Datetime($result_array[$i]['updatedAt']['value']));
            $converter->setScriptLanguage($result_array[$i]['scriptLanguage']['value']);
            
            if (array_key_exists('workflow', $result_array[$i]))
            {
                $workflow = new \AppBundle\Entity\Workflow();
                $workflow->setUri($result_array[$i]['workflow']['value']);
                $converter->setWorkflow($workflow);
            }
            
            if (array_key_exists('wro', $result_array[$i]))
            {
                $wro = new \AppBundle\Entity\WRO();
                $wro->setUri($result_array[$i]['wro']['value']);
                $converter->setWro($wro);
            }
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[$i]['creator']['value']);
            $creator->setName($result_array[$i]['name']['value']);
            $converter->setCreator($creator);
                
            $conversion[] = $converter;    
        }
        return $conversion;
    }
    
    public function updateScriptConversion(\AppBundle\Entity\ScriptConverter $conversion)
    {
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph('scriptconverter').">
        DELETE 
        { 
            <".$conversion->getUri()."> a <w2share:ScriptConversion>.
            <".$conversion->getUri()."> <w2share:hash> ?hash.
            <".$conversion->getUri()."> <w2share:scriptLanguage> ?scriptLanguage.
            <".$conversion->getUri()."> <w2share:createdAt> ?createdAt.
            <".$conversion->getUri()."> <w2share:updatedAt> ?updatedAt.
            <".$conversion->getUri()."> <w2share:hasWorkflow> ?workflow.
            <".$conversion->getUri()."> <w2share:hasWorkflowResearchObject> ?wro.
            <".$conversion->getUri()."> <w2share:updatedAt> ?updatedAt.
            <".$conversion->getUri()."> <dc:creator> ?creator. 
        }
        INSERT
        {
            <".$conversion->getUri()."> a <w2share:ScriptConversion>.
            <".$conversion->getUri()."> <w2share:hash> '".$conversion->getHash()."'.
            <".$conversion->getUri()."> <w2share:scriptLanguage> '".$conversion->getScriptLanguage()."'.
            <".$conversion->getUri()."> <w2share:createdAt> '".$conversion->getCreatedAt()->format(\DateTime::ISO8601)."'.
            <".$conversion->getUri()."> <w2share:updatedAt> '".$conversion->getUpdatedAt()->format(\DateTime::ISO8601)."'.
            <".$conversion->getUri()."> <dc:creator> <".$conversion->getCreator()->getUri().">.
        ";
        
        if ($conversion->getWorkflow())
        {
            $query .= "<".$conversion->getUri()."> <w2share:hasWorkflow> <".$conversion->getWorkflow()->getUri().">.\n";
        }
        if ($conversion->getWro())
        {
            $query .= "<".$conversion->getUri()."> <w2share:hasWorkflowResearchObject> <".$conversion->getWro()->getUri().">.\n";
        }
        
        $query .= "}
        WHERE 
        { 
            <".$conversion->getUri()."> a <w2share:ScriptConversion>.
            <".$conversion->getUri()."> <w2share:hash> ?hash.
            <".$conversion->getUri()."> <w2share:scriptLanguage> ?scriptLanguage.
            <".$conversion->getUri()."> <w2share:createdAt> ?createdAt.
            OPTIONAL { <".$conversion->getUri()."> <w2share:hasWorkflow> ?workflow. }
            OPTIONAL { <".$conversion->getUri()."> <w2share:hasWorkflowResearchObject> ?wro. }
            <".$conversion->getUri()."> <w2share:updatedAt> ?updatedAt.
            <".$conversion->getUri()."> <dc:creator> ?creator. 
        }"; 
        return $this->driver->getResults($query);
    }
    
    public function deleteScriptConversion(\AppBundle\Entity\ScriptConverter $conversion)
    {
        $workflow = $conversion->getWorkflow();
        if ($workflow)
        {
            $model = $this->container->get('model.workflow');
            $model->deleteWorkflow($workflow);
        }
        
        $wro = $conversion->getWro();
        if ($wro)
        {
            $model = $this->container->get('model.wro');
            $model->deleteWro($wro);
        }
        
        $query = 
        "DELETE data FROM <".$this->driver->getDefaultGraph('scriptconverter')."> { 
            <".$conversion->getUri()."> a <w2share:ScriptConversion>.
            <".$conversion->getUri()."> <w2share:hash> '".$conversion->getHash()."'.
            <".$conversion->getUri()."> <w2share:scriptLanguage> '".$conversion->getScriptLanguage()."'.
            <".$conversion->getUri()."> <w2share:createdAt> '".$conversion->getCreatedAt()->format(\DateTime::ISO8601)."'.
            <".$conversion->getUri()."> <w2share:updatedAt> '".$conversion->getUpdatedAt()->format(\DateTime::ISO8601)."'.
            <".$conversion->getUri()."> <dc:creator> <".$conversion->getCreator()->getUri().">. 
        "; 
        
        if ($conversion->getWorkflow())
        {
            $query .= "<".$conversion->getUri()."> <w2share:hasWorkflow> <".$conversion->getWorkflow()->getUri().">.\n";
        }
        if ($conversion->getWro())
        {
            $query .= "<".$conversion->getUri()."> <w2share:hasWorkflowResearchObject> <".$conversion->getWro()->getUri().">.\n";
        }
        
        $query .= "}";
        
        $this->driver->getResults($query);
        
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/w2share/".$conversion->getHash());
        rmdir(__DIR__."/../../../web/uploads/documents/w2share/".$conversion->getHash());        
    }
    
    public function addWorkflow(\AppBundle\Entity\Workflow $workflow)
    {                        
        $this->editWorkflow($workflow);
        $this->saveWorkflowHash($workflow); 
        $this->addWorkflowIntoConversion($workflow);
    }
    
    public function addWorkflowIntoConversion(\AppBundle\Entity\Workflow $workflow)
    {
        $conversion = $this->findOneScriptConversionByHash($workflow->getHash());
        $conversion->setWorkflow($workflow);
        $this->updateScriptConversion($conversion);
    }
    
    public function getURIFromWfdesc(\AppBundle\Entity\Workflow $workflow)
    {
        \EasyRdf_Namespace::set('ro', 'http://purl.org/wf4ever/ro#');
        \EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
        \EasyRdf_Namespace::set('ore', 'http://www.openarchives.org/ore/terms/');
        
        $graph = new \EasyRdf_Graph();
        $graph->parseFile($workflow->getWfdescAbsolutePath());
        $resources = $graph->allOfType('http://purl.org/wf4ever/wfdesc#Workflow');
        
        foreach ($resources as $resource)
        {
            $workflow->setUri($resource->getUri());
        }
    }
    
    public function editWorkflow(\AppBundle\Entity\Workflow $workflow)
    {       
        if ($workflow->getProvenanceFile())
        {
            $this->driver->load($workflow->getWebPath()."/".basename($workflow->getProvenanceAbsolutePath()));
        }                
        
        if ($workflow->getWorkflowFile())
        {
            $workflow->createWorkflowPNG();
            $workflow->createWfdescFile();
            $this->driver->load($workflow->getWebPath()."/".basename($workflow->getWfdescAbsolutePath()));
            $this->getURIFromWfdesc($workflow);
        }
    }
    
    private function saveWorkflowHash(\AppBundle\Entity\Workflow $workflow) 
    {      
        $query = 
        "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph()."> 
            { 
                <".$workflow->getUri()."> <w2share:hash> '".$workflow->getHash()."'. 
            }
        }"; 
        $this->driver->getResults($query);       
    } 
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('scriptconverter').">";        
        return $this->driver->getResults($query);              
    }
    
    public function clearUploads ()
    {            
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/w2share");
    }               
}