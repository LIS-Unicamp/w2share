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
    
    public function __construct($driver)
    {
        $this->driver = $driver;
    }        
    
    public function clearUploads ($root_path)
    {       
        foreach(glob($root_path."/../web/uploads/documents/*.*") as $file)
        {            
            unlink($file);
        }
    }  
    
    public function findAll()
    {
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?uri a wfdesc:Workflow.
                ?uri dc:creator ?creator.
                OPTIONAL { ?uri rdfs:label ?label. }
                OPTIONAL { ?uri dcterms:description ?description. }
                OPTIONAL { ?uri dcterms:title ?title. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function findProcessesByWorkflow($workflow)
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
    
    public function saveWorkflow($workflow)
    {
        $this->load($workflow->getProvenanceAbsolutePath());
        $this->load($workflow->getWfdescAbsolutePath());        

        $command = "ruby ".$root_path."/../src/AppBundle/Utils/script.rb ".$workflow->getWorkflowAbsolutePath()." ".$root_path."/../web/uploads/documents/".$workflow->getHash().".png";            
        system($command);
    }
    
    protected function load($file_path)
    {        
        $query = "LOAD <http://".$this->driver->getDomain()."/prototype/web/uploads/documents/".basename($file_path)."> INTO graph <".$this->driver->getDefaultGraph().">";
        $this->driver->getResults($query);
    }
    
    /**
     * Delete triples related to a workflow URI
     * @param type $workflow_uri
     */
    public function deleteWorkflow($workflow_uri)
    {
        $query = "
            DELETE data FROM <".$this->driver->getDefaultGraph()."> {
                <".$workflow_uri."> rdf:type wfdesc:Workflow.                
            }
            ";  
        return $this->driver->getResults($query);
    }
    
    public function workflows()
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflow rdf:type wfdesc:Workflow.
                OPTIONAL { ?workflow dcterms:description ?description. }
                OPTIONAL { ?workflow dcterms:title ?title. }
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
