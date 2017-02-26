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
        $this->load($ro->getROAbsolutePath());
        $this->saveROHash($ro);        
    }
    
    private function saveROHash(\AppBundle\Entity\ResearchObject $ro) 
    {      
        $query = 
        "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph()."> 
            { 
                <".$ro->getUri()."> <w2share:hash> '".$ro->getHash()."'. 
            }
        }"; 

        return $this->driver->getResults($query);        
    }
    
    protected function load($file_path)
    {        
        $env = $this->container->get('kernel')->getEnvironment();
        
        $path_url = '';
        if ($env == 'dev')
        {
            $path_url = "http://"
                . $this->container->get('request')->getHost();
        }
        $path_url .= $this->container->get('templating.helper.assets')
                ->getUrl("/uploads/documents/".basename($file_path), null, true, true);
        
        $query = "LOAD <".$path_url."> INTO graph <".$this->driver->getDefaultGraph().">";
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