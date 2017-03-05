<?php
namespace AppBundle\Model;

/**
 * Description of the Research Object model
 *
 * @author lucas
 */
class ResearchObject
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
            ?uri dct:created ?createdAt.
            ?uri dct:creator ?creator.            
        }";
       
        $ros = $this->driver->getResults($query);
        
        $ro_array = array();
        
        for ($i = 0; $i < count($ros); $i++)
        {   
            $ro = new \AppBundle\Entity\ResearchObject();            
            $ro->setUri($ros[$i]['uri']['value']);
            $ro->setCreatedAt($ros[$i]['createdAt']['value']);            
            $ro->setCreator($ros[$i]['creator']['value']);                        
            
            $ro_array[] = $ro;
        } 
        
        return $ro_array;
    }
    
    public function addResearchObject(\AppBundle\Entity\ResearchObject $ro)
    {
        $this->unzipROBundle($ro);
        $this->findManifest($ro);
        $this->loadFiles($ro);
        $this->saveROHash($ro);        
    }
    
    private function saveROHash(\AppBundle\Entity\ResearchObject $ro) 
    {      
        $query = 
        "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('ro')."> 
            { 
                <".$ro->getUri()."> <w2share:hash> '".$ro->getHash()."'. 
            }
        }"; 

        return $this->driver->getResults($query);        
    }
    
    private function findManifest(\AppBundle\Entity\ResearchObject $ro)
    {
        if (file_exists($this->getRODirPath($ro)."/.ro/manifest.json"))
        {
            $this->loadManifestJSON($ro); 
        }
        else if (file_exists($this->getRODirPath($ro)."/.ro/manifest.rdf"))
        {
            $env = $this->container->get('kernel')->getEnvironment();
        
            $path_url = '';
            if ($env == 'dev')
            {
                $path_url = "http://"
                    . $this->container->get('request')->getHost();
            }
            $path_url .= $this->container->get('templating.helper.assets')
                    ->getUrl("/uploads/documents/ro/".$ro->getHash()."/.ro/manifest.rdf", null, true, true);
            
            $this->loadManifestRDF($path_url);
        }
    }
    
    private function loadManifestJSON(\AppBundle\Entity\ResearchObject $ro)
    {
        $str = file_get_contents($this->getRODirPath($ro)."/.ro/manifest.json");
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
                $this->unzipWFBundle($ro, $aggregate['content']);
            }
        } 
    }
    
    private function loadManifestRDF(\AppBundle\Entity\ResearchObject $ro)
    {
        $path_url = $this->getRODirPath($ro)."/.ro/manifest.rdf";
        
        \EasyRdf_Namespace::set('ro', 'http://purl.org/wf4ever/ro#');
        \EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
        \EasyRdf_Namespace::set('ore', 'http://www.openarchives.org/ore/terms/');

        $graph = new \EasyRdf_Graph($path_url);
        $graph->load();
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
    
    private function unzipWFBundle(\AppBundle\Entity\ResearchObject $ro, $wfbundle_filename)
    {            
        $wfbundle_path = $this->getRODirPath($ro).$wfbundle_filename;
        $zip = new \ZipArchive; 
        if ($zip->open($wfbundle_path))
        { 
            try 
            {
                $zip->extractTo($this->getRODirPath($ro)); 
                $zip->close(); 
            }
            catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
            {
                $e->getMessage();
            }
            
        }
        @unlink($wfbundle_path);
    }
    
    private function unzipROBundle(\AppBundle\Entity\ResearchObject $ro)
    {
            
        $zip = new \ZipArchive; 
        if ($zip->open($ro->getROAbsolutePath()))
        { 
            $zip->extractTo($this->getRODirPath($ro)); 
            $zip->close(); 
        }
        unlink($ro->getROAbsolutePath());
    }
    
    private function getRODirPath(\AppBundle\Entity\ResearchObject $ro)
    {
        $root_path = $this->container->get('kernel')->getRootDir();

        return $root_path."/../web/uploads/documents/ro/".$ro->getHash();
    }
    
    private function loadIntoDB(\AppBundle\Entity\ResearchObject $ro, $file_path)
    {        
        $env = $this->container->get('kernel')->getEnvironment();
        
        $path_url = '';
        if ($env == 'dev')
        {
            $path_url = "http://"
                . $this->container->get('request')->getHost();
        }
        $path_url .= $this->container->get('templating.helper.assets')
                ->getUrl("/uploads/documents/ro/".$ro->getHash()."/".$file_path, null, true, true);
        
        $query = "LOAD <".$path_url."> INTO graph <".$this->driver->getDefaultGraph('ro').">";
        $this->driver->getResults($query);
    }
            
    public function clearGraph()
    {
        $root_path = $this->container->get('kernel')->getRootDir();

        foreach(glob($root_path."/../web/uploads/documents/ro/*.*") as $file)
        {            
            unlink($file);
        }
        
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('ro').">";        
        return $this->driver->getResults($query);                  
    }
    
}