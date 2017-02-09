<?php
namespace AppBundle\Model;

/**
 * Description of Workflow
 *
 * @author lucas
 */
class Workflow 
{
    private $driver;
    protected $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $driver)
    {
        $this->driver = $driver;
        $this->em = $em;
    }
    
    public function clearDB ()
    {
        $this->em->createQuery('DELETE FROM AppBundle:Workflow')->execute();
    }
    
    public function clearUploads ($root_path)
    {       
        foreach(glob($root_path."/../web/uploads/documents/*.*") as $file)
        {            
            unlink($file);
        }
    }  
    
    public function processes($workflow)
    {
        // process information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <$workflow> wfdesc:hasSubProcess ?process.
                ?process a wfdesc:Process.
                OPTIONAL { ?process rdfs:label ?label. }
                OPTIONAL { ?process dcterms:description ?description. }
                OPTIONAL { ?process prov:specializationOf ?workflow. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph().">";        
        return $this->driver->getResults($query);              
    }
}
