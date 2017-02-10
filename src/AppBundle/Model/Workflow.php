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
        
        $workflow_array = array();
        $workflows = $this->driver->getResults($query);   
        
        for ($i = 0; $i < count($workflows); $i++)
        {
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($workflows[$i]['uri']['value']);
            $workflow->setTitle($workflows[$i]['title']['value']);
            $workflow->setDescription($workflows[$i]['description']['value']);
            $workflow->setLabel($workflows[$i]['label']['value']);
            
//            $creator = new \AppBundle\Entity\Person();
//            $creator->setName($workflows[$i]['creator_name']['value']);
//            $creator->setUri($workflows[$i]['creator']['value']);

            $workflow->setCreator($workflows[$i]['creator']['value']);
            
            $workflow_array[] = $workflow;  
        }
        
        return $workflow_array;
    }
    
    public function findWorkflow($workflow_uri)
    {
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow_uri."> a wfdesc:Workflow.
                <".$workflow_uri."> dc:creator ?creator.
                OPTIONAL { <".$workflow_uri."> rdfs:label ?label. }
                OPTIONAL { <".$workflow_uri."> dcterms:description ?description. }
                OPTIONAL { <".$workflow_uri."> dcterms:title ?title. }
            }}
            ";
        
        $workflow_array = $this->driver->getResults($query);   
        
        if (count($workflow_array) > 0)
        {
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($workflow_uri);
            $workflow->setTitle($workflow_array[0]['title']['value']);
            $workflow->setDescription($workflow_array[0]['description']['value']);
            $workflow->setLabel($workflow_array[0]['label']['value']);            
            $workflow->setCreator($workflow_array[0]['creator']['value']);    
            return $workflow;
        }
        
        return null;
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
    
    public function findWorkflowInputs($workflow_uri)
    {
        // inputs information
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow_uri."> a wfdesc:Workflow;
                wfdesc:hasInput ?input.
                OPTIONAL { ?input rdfs:label ?label. }
                OPTIONAL { ?input dcterms:description ?description. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function findWorkflowOutputs($workflow_uri)
    {
        // outputs information
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow_uri."> a wfdesc:Workflow;
                wfdesc:hasOutput ?output.
                OPTIONAL { ?output rdfs:label ?label. }
                OPTIONAL { ?output dcterms:description ?description. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function findProcessOutputs($process_uri)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process_uri."> a wfdesc:Process;
                wfdesc:hasOutput ?output.
                OPTIONAL { ?output rdfs:label ?label }
                OPTIONAL { ?output dcterms:description ?description }
            }}
            ";
        
        $output_array = array();
        $outputs = $this->driver->getResults($query);   
        
        for ($i = 0; $i < count($outputs); $i++)
        {
            $output = new \AppBundle\Entity\Output();
            $output->setUri($outputs[$i]['output']['value']);
            if (in_array('description', $outputs[$i]))
            {
                $output->setDescription($outputs[$i]['description']['value']);
            }
            $output->setLabel($outputs[$i]['label']['value']);            
            
            $output_array[] = $output;  
        }
        
        return $output_array;
    }
    
    public function findProcessInputs($process_uri)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process_uri."> a wfdesc:Process;
                wfdesc:hasInput ?input.
                OPTIONAL { ?input rdfs:label ?label }
                OPTIONAL { ?input dcterms:description ?description }
            }}
            ";
        
        $input_array = array();
        $inputs = $this->driver->getResults($query);   
        
        for ($i = 0; $i < count($inputs); $i++)
        {
            $input = new \AppBundle\Entity\Input();
            $input->setUri($inputs[$i]['input']['value']);
            if (in_array('description', $inputs[$i]))
            {
                $input->setDescription($inputs[$i]['description']['value']);
            }
            $input->setLabel($inputs[$i]['label']['value']);            
            
            $input_array[] = $input;  
        }
        
        return $input_array;
    }
    
    public function findProcess($process_uri)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process_uri."> a wfdesc:Process.
                ?workflow wfdesc:hasSubProcess <".$process_uri.">.
                OPTIONAL { <".$process_uri."> rdfs:label ?label }
                OPTIONAL { <".$process_uri."> dcterms:description ?description }
                OPTIONAL { <".$process_uri."> prov:specializationOf ?subworkflow. }
            }}
            ";
        
        $process_array = $this->driver->getResults($query);   
        
        if (count($process_array) > 0)
        {
            $process = new \AppBundle\Entity\Process();
            $process->setUri($process_uri);
            $process->setDescription($process_array[0]['description']['value']);
            $process->setLabel($process_array[0]['label']['value']);            
                
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($process_array[0]['workflow']['value']);
            $process->setWorkflow($workflow);
            
            return $process;
        }
        
        return null;
    }
    
    public function saveWorkflow(\AppBundle\Entity\Workflow $workflow, $root_path)
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
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph().">";        
        return $this->driver->getResults($query);              
    }
}
