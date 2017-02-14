<?php
namespace AppBundle\Model;

/**
 * Description of Provenance
 *
 * @author lucas
 */
class Provenance 
{
    private $driver;
        
    public function __construct($driver)
    {
        $this->driver = $driver;
    }                    
    
    public function findWorkflowsRunByWorkflowOrAll($workflow_uri = null)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflowRun rdf:type wfprov:WorkflowRun.
                ?workflowRun rdfs:label ?label.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
                ?workflowRun wfprov:describedByWorkflow ".(($workflow_uri)?"<".$workflow_uri.">":"?workflow").".
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        $workflows_run_array = $this->driver->getResults($query);
        
        $workflowsRun = array();
        for ($i = 0; $i < count($workflows_run_array); $i++)
        {
            $workflowRun = new \AppBundle\Entity\WorkflowRun();
            $workflowRun->setUri($workflows_run_array[$i]['workflowRun']['value']);
            $workflowRun->setLabel($workflows_run_array[$i]['label']['value']);
            $workflowRun->setStartedAtTime(new \Datetime($workflows_run_array[$i]['startedAtTime']['value']));
            $workflowRun->setEndedAtTime(new \Datetime($workflows_run_array[$i]['endedAtTime']['value']));
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($workflows_run_array[$i]['workflow']['value']);
            $workflowRun->setWorkflow($workflow);
            
            $workflowsRun[] = $workflowRun;
        }

        return $workflowsRun;
    }
    
    /**
     * List of processes executed in a workflow run
     * @param type $workflow_run
     * @return array
     */
    public function findProcessesByWorkflowRun($workflow_run_uri)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?processRun wfprov:describedByProcess ?process.
                ?processRun a wfprov:ProcessRun.
                ?processRun rdfs:label ?label.
                ?processRun prov:endedAtTime ?endedAtTime.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?processRun wfprov:wasPartOfWorkflowRun <".$workflow_run_uri.">.
            }}
            ORDER BY  DESC(?startedAtTime)
        ";

        $processes_run_array = $this->driver->getResults($query);
        $processes = array();
        
        for ($i = 0; $i < count($processes_run_array); $i++)
        {
            $processRun = new \AppBundle\Entity\ProcessRun();
                        
            $processRun->setUri($processes_run_array[$i]['processRun']['value']);
            $processRun->setLabel($processes_run_array[$i]['label']['value']);
            $processRun->setStartedAtTime(new \Datetime($processes_run_array[$i]['startedAtTime']['value']));
            $processRun->setEndedAtTime(new \Datetime($processes_run_array[$i]['endedAtTime']['value']));
            
            $process = new \AppBundle\Entity\Process();
            $process->setUri($processes_run_array[$i]['process']['value']);
            $processRun->setProcess($process);
            
            $workflowRun = new \AppBundle\Entity\WorkflowRun();
            $workflowRun->setUri($workflow_run_uri);
            $processRun->setWorkflowRun($workflowRun);     
            
            $processes[] = $processRun;
        }

        return $processes;
    }
    
    /**
     * List of processes executed in a workflow run
     * @param type $workflow_run
     * @return \AppBundle\Entity\WorkflowRun
     */
    public function findWorkflowRun($workflow_run_uri)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow_run_uri."> rdf:type wfprov:WorkflowRun.
                <".$workflow_run_uri."> rdfs:label ?label.
                <".$workflow_run_uri."> prov:endedAtTime ?endedAtTime.
                <".$workflow_run_uri."> prov:startedAtTime ?startedAtTime.
                <".$workflow_run_uri."> wfprov:describedByWorkflow ?workflow.
            }}         
        ";

        $workflow_run_array = $this->driver->getResults($query);
        
        if (count($workflow_run_array) > 0)
        {
            $workflowRun = new \AppBundle\Entity\WorkflowRun();
            $workflowRun->setUri($workflow_run_uri);
            $workflowRun->setLabel($workflow_run_array[0]['label']['value']);
            $workflowRun->setStartedAtTime(new \Datetime($workflow_run_array[0]['startedAtTime']['value']));
            $workflowRun->setEndedAtTime(new \Datetime($workflow_run_array[0]['endedAtTime']['value']));
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($workflow_run_array[0]['workflow']['value']);
            $workflowRun->setWorkflow($workflow);            
        
            return $workflowRun;
        }
        
        return null;        
    }        
    
    public function findProcessRun($process_uri)
    {
        // Process information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun prov:endedAtTime ?endedAtTime.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?processRun wfprov:wasPartOfWorkflowRun ?workflowRun.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function findInputsByProcessRun($process_uri)
    {
        // inputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun wfprov:usedInput ?usedInput.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?usedInput tavernaprov:content ?content.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function findOutputsByProcessRun($process_run_uri)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?output prov:wasGeneratedBy ?processRun.
                ?output tavernaprov:content ?content.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function findOutputsByWorkflowRun($workflow_run_uri)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?outputRun wfprov:wasOutputFrom <".$workflow_run_uri.">.
                ?outputRun a wfprov:ProcessRun.
                ?outputRun tavernaprov:content ?content.
            }}
            ";
        
        $outputs_array = $this->driver->getResults($query);
        
        $outputs = array();
        for ($i = 0; $i < count($outputs_array); $i++)
        {
            $workflowRun = new \AppBundle\Entity\WorkflowRun();
            $workflowRun->setUri($workflow_run_uri);
                         
            $outputRun = new \AppBundle\Entity\OutputRun();
            $outputRun->setUri($outputs_array[$i]['outputRun']['value']);
            $outputRun->setContent($outputs_array[$i]['content']['value']);
            $outputRun->setWorkflowRun($workflowRun);            
        
            $outputs[] = $outputRun;
        }
        
        return $outputs;  
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph().">";        
        return $this->driver->getResults($query);                
    }
}
