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
        $this->unzip($ro);
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
        if (file_exists($this->getDirPath($ro)."/.ro/manifest.json"))
        {
            $str = file_get_contents($this->getDirPath($ro)."/.ro/manifest.json");
            $manifest_data = json_decode($str, true); // decode the JSON into an associative array
            
            foreach ($manifest_data['aggregates'] as $aggregate)
            {
                echo $aggregate['folder'].$aggregate['file'].'<br>';
            }            
        }
        else if (file_exists($this->getDirPath($ro)."/.ro/manifest.rdf"))
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

            echo $path_url;
            $foaf = new \EasyRdf_Graph($path_url);
            $foaf->load();
            print_r($foaf);
            foreach ($aggregates as $aggregate)
            {
                echo $aggregate['folder'].$aggregate['file'].'<br>';
            }
            exit;
        }

    }
    
    private function unzip(\AppBundle\Entity\ResearchObject $ro)
    {
            
        $zip = new \ZipArchive; 
        if ($zip->open($ro->getROAbsolutePath()))
        { 
            $zip->extractTo($this->getDirPath($ro)); 
            $zip->close(); 
        }
    }
    
    private function getDirPath(\AppBundle\Entity\ResearchObject $ro)
    {
        $root_path = $this->container->get('kernel')->getRootDir();

        return $root_path."/../web/uploads/documents/ro/".$ro->getHash();
    }
    
    private function load(\AppBundle\Entity\ResearchObject $ro)
    {        
        $file_path = $ro->getROAbsolutePath();
        $env = $this->container->get('kernel')->getEnvironment();
        
        $path_url = '';
        if ($env == 'dev')
        {
            $path_url = "http://"
                . $this->container->get('request')->getHost();
        }
        $path_url .= $this->container->get('templating.helper.assets')
                ->getUrl("/uploads/documents/ro/".basename($file_path), null, true, true);
        
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