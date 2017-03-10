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
            ?uri dct:created ?createdAt.
            ?uri dct:creator ?creator. 
            ?creator foaf:name ?name.
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
    
    public function addWRO(\AppBundle\Entity\WRO $wro)
    {
        $this->unzipWROBundle($wro);
        $this->findManifest($wro);
        //$this->loadFiles($wro);
        $this->saveWROHash($wro);        
    }
    
    private function saveWROHash(\AppBundle\Entity\WRO $wro) 
    {      
        $query = 
        "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('wro')."> 
            { 
                <".$wro->getUri()."> <w2share:hash> '".$wro->getHash()."'. 
            }
        }"; 

        return $this->driver->getResults($query);        
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
}