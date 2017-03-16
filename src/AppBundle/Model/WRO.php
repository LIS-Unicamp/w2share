<?php
namespace AppBundle\Model;

/**
 * Description of the Research Object model
 *
 * @author lucas
 */
class WRO
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
    
    public function addWRO(\AppBundle\Entity\WRO $wro)
    {
        $this->unzipWROBundle($wro);
        $this->findManifest($wro);
        //$this->loadFiles($wro);
        $this->saveWROHash($wro);        
    }
    
    private function saveWroScriptConversion(\AppBundle\Entity\WRO $wro) 
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
    
    private function findManifest(\AppBundle\Entity\WRO $wro)
    {
        if (file_exists($this->getWRODirPath($wro)."/.ro/manifest.json"))
        {
            $this->loadManifestJSON($wro); 
        }
        else if (file_exists($this->getWRODirPath($wro)."/.ro/manifest.rdf"))
        {            
            $this->loadManifestRDF($wro);
        }
    }
    
    private function loadManifestJSON(\AppBundle\Entity\WRO $wro)
    {
        $str = file_get_contents($this->getWRODirPath($wro)."/.ro/manifest.json");
        $manifest_data = json_decode($str, true); // decode the JSON into an associative array

        foreach ($manifest_data['aggregates'] as $aggregate)
        {
            $aggregate['folder'].$aggregate['file'];
            if (in_array('mediatype', $aggregate))
            {
                $aggregate['mediatype'];
            }
        }    
        
        foreach ($manifest_data['annotations'] as $aggregate)
        {
            $aggregate['about'].$aggregate['content'];
            if (in_array('annotation', $aggregate))
            {
                $aggregate['annotation'];
            }
            
            if ($aggregate['content'] == '/workflow.wfbundle')
            {
                $this->unzipWFBundle($wro, $aggregate['content']);
            }
        } 
    }
    
    private function loadManifestRDF(\AppBundle\Entity\WRO $wro)
    {
        $path_url = $this->getWRODirPath($wro)."/.ro/manifest.rdf";
        
        \EasyRdf_Namespace::set('ro', 'http://purl.org/wf4ever/ro#');
        \EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
        \EasyRdf_Namespace::set('ore', 'http://www.openarchives.org/ore/terms/');

        $graph = new \EasyRdf_Graph();
        $graph->parseFile($path_url);
        //print_r($graph);
        $resource = $graph->resourcesMatching('ore:aggregates');
        $aggregates = $graph->AllResources($resource[0],'ore:aggregates');
        //echo($resource);
        foreach ($aggregates as $aggregate)
        {
            $aggregate.$aggregate->type();
        }
        exit;
    }
    
    private function unzipWFBundle(\AppBundle\Entity\WRO $wro, $wfbundle_filename)
    {            
        $wfbundle_path = $this->getWRODirPath($wro).$wfbundle_filename;
        $zip = new \ZipArchive; 
        if ($zip->open($wfbundle_path))
        { 
            try 
            {
                $zip->extractTo($this->getWRODirPath($wro)); 
                $zip->close(); 
            }
            catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
            {
                $e->getMessage();
            }
            
        }
        @unlink($wfbundle_path);
    }
    
    private function unzipWROBundle(\AppBundle\Entity\WRO $wro)
    {
            
        $zip = new \ZipArchive; 
        if ($zip->open($wro->getWROAbsolutePath()))
        { 
            $zip->extractTo($this->getWRODirPath($wro)); 
            $zip->close(); 
        }
        unlink($wro->getWROAbsolutePath());
    }
    
    private function getWRODirPath(\AppBundle\Entity\WRO $wro)
    {
        $wroot_path = $this->container->get('kernel')->getRootDir();

        return $wroot_path."/../web/uploads/documents/wro/".$wro->getHash();
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
    
    public function clearUploads()
    {
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/wro");
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
    
    public function createWRO(\AppBundle\Entity\ScriptConverter $conversion)
    {
        $wro = new \AppBundle\Entity\WRO();        
        $wro->setHash($conversion->getHash());
        $wro_uri = $uri = \AppBundle\Utils\Utils::convertNameToUri("Workflow Research Object", $wro->getHash());
        $wro->setUri($wro_uri);
        $wro->setCreator($conversion->getCreator());
        $wro->setWorkflow($conversion->getWorkflow());
        $wro->setScriptConversion($conversion);
        $this->addResources($wro);
        //$wro->addResource($resources);
        $this->saveWRO($wro);
        $this->saveWroScriptConversion($wro);
        $this->createWROScript($wro);
    }
    
    public function addResources(\AppBundle\Entity\WRO $wro)
    {
        if ($wro->getWorkflow())
        {
            $wro->addResourceBuilder($wro->getWorkflow()->getUri(), basename($wro->getWorkflow()->getWorkflowAbsolutePath()), '', 'Workflow specification', 'wfdesc:Workflow');
        }
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
        $wro->removeUpload();        
    }
    
    public function createWROScript(\AppBundle\Entity\WRO $wro)
    {
        //TODO: add rdf:type wf4ever:WorkflowResearchObject
        $code = "#!/bin/bash                   
                cd ".$wro->getUploadRootDir()."/../
                ROBASE=\"wro\"

                ro config -v \
                  -b ".'$ROBASE'." \
                  -r http://sandbox.wf4ever-project.org/rodl/ROs/ \
                  -t \"".$wro->getHash()."\" \
                  -n \"Lucas\" \
                  -e \"lucas.carvalho@ic.unicamp.br\"

                mkdir  ".'$ROBASE'."/test-create-RO

                rm -rf ".'$ROBASE'."/test-create-RO/.ro
                    
                rsync -aP --exclude=".'$ROBASE'." --exclude=create-wro.sh --exclude=conversion.py --exclude=wf.gv . ".'$ROBASE'."/test-create-RO

                ro create -v \"Reproducible WRO\" -d ".'$ROBASE'."/test-create-RO -i RO-id-testCreate

                ro add -v -a ".'$ROBASE'."/test-create-RO -d ".'$ROBASE'."/test-create-RO\n";

        foreach ($wro->getResources() as $resource)
        {
            $code .= "ro annotate -v ".'$ROBASE'."/test-create-RO/".$resource->getFolder()."/".$resource->getFilename()." rdf:type \"".$resource->getType()."\"\n";
            $code .= "ro annotate -v ".'$ROBASE'."/test-create-RO/".$resource->getFolder()."/".$resource->getFilename()." title \"".$resource->getDescription()."\"\n";
        }
        $code .= 'cd '.$wro->getUploadRootDir().'
                echo -n application/vnd.wf4ever.robundle+zip > '.$wro->getUploadRootDir().'/mimetype

                zip -0 -X '.$wro->getWROAbsolutePath().' mimetype  

                zip -X -r '.$wro->getWROAbsolutePath().' . -x mimetype';
        
        $fs = new \Symfony\Component\Filesystem\Filesystem();           
        $fs->dumpFile($wro->getWROScriptAbsolutePath(), $code);
        exec('sh '.$wro->getWROScriptAbsolutePath());
    }
}