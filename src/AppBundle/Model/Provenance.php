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
    
    public function findWorkflowsRunsByWorkflowOrAll($workflow_uri = null)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflowRun rdf:type wfprov:WorkflowRun.
                ?workflowRun rdfs:label ?label.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
                ?workflowRun wfprov:describedByWorkflow ".(($workflow_uri)?"<".$workflow_uri.">":"?workflow").".
                ".(($workflow_uri)?"<".$workflow_uri.">":"?workflow")." dcterms:title ?title.
                ".(($workflow_uri)?"<".$workflow_uri.">":"?workflow")." dcterms:description ?description.
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
            $workflow->setUri(($workflow_uri)?$workflow_uri:$workflows_run_array[$i]['workflow']['value']);
            $workflow->setTitle(($workflow_uri)?$workflow_uri:$workflows_run_array[$i]['title']['value']);
            $workflow->setDescription(($workflow_uri)?$workflow_uri:$workflows_run_array[$i]['description']['value']);
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
    public function findProcessesRunByWorkflowRun($workflow_run_uri)
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
    
    public function findProcessRun($process_run_uri)
    {
        // Process information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process_run_uri."> wfprov:describedByProcess ?process.
                <".$process_run_uri."> a wfprov:ProcessRun.
                <".$process_run_uri."> rdfs:label ?label.
                <".$process_run_uri."> prov:endedAtTime ?endedAtTime.
                <".$process_run_uri."> prov:startedAtTime ?startedAtTime.
            }}
            ";
        
        $result_array = $this->driver->getResults($query);
        
        if (count($result_array) > 0)
        {
            $processRun = new \AppBundle\Entity\ProcessRun();
            $processRun->setUri($process_run_uri);
            $processRun->setLabel($result_array[0]['label']['value']);
            $processRun->setStartedAtTime(new \Datetime($result_array[0]['startedAtTime']['value']));
            $processRun->setEndedAtTime(new \Datetime($result_array[0]['endedAtTime']['value']));
            
            $process = new \AppBundle\Entity\Process();
            $process->setUri($result_array[0]['process']['value']);
            $processRun->setProcess($process);
            return $processRun;
        }
        
        return null;
    }
    
    public function findProcessRunsByProcess($process_uri)
    {
        // Process information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?processRun wfprov:describedByProcess <".$process_uri.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun rdfs:label ?label.
                ?processRun prov:endedAtTime ?endedAtTime.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?processRun wfprov:wasPartOfWorkflowRun ?workflowRun.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        $result_array = $this->driver->getResults($query);
        $processRuns = array();
        
        for ($i = 0; $i < count($result_array); $i++)
        {
            $processRun = new \AppBundle\Entity\ProcessRun();
            $processRun->setUri($result_array[0]['processRun']['value']);
            $processRun->setLabel($result_array[0]['label']['value']);
            $processRun->setStartedAtTime(new \Datetime($result_array[0]['startedAtTime']['value']));
            $processRun->setEndedAtTime(new \Datetime($result_array[0]['endedAtTime']['value']));
            
            $process = new \AppBundle\Entity\Process();
            $process->setUri($process_uri);
            $processRun->setProcess($process);
            
            $workflowRun = new \AppBundle\Entity\WorkflowRun();
            $workflowRun->setUri($result_array[0]['workflowRun']['value']);
            $processRun->setWorkflowRun($workflowRun);     
            
            $processRuns[] = $processRun;
        }
        
        return $processRuns;
    }
    
    public function findInputsRunByProcessRun($process_run_uri)
    {
        // inputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process_run_uri."> a wfprov:ProcessRun.
                <".$process_run_uri."> wfprov:usedInput ?inputRun.
                ?inputRun tavernaprov:content ?content.
                ?inputRun wfprov:describedByParameter ?input.
                ?input a wfdesc:Input.
                OPTIONAL { ?input dcterms:description ?description. }
                ?input rdfs:label ?label.
                ?process  wfdesc:hasInput ?input.
            }}
            ";
        
        $inputs_array = $this->driver->getResults($query);
        
        $inputs = array();
        for ($i = 0; $i < count($inputs_array); $i++)
        {
            $processRun = new \AppBundle\Entity\ProcessRun();
            $processRun->setUri($process_run_uri);
            //$workflowRun = new \AppBundle\Entity\WorkflowRun();
            //$workflowRun->setUri($workflow_run_uri);
                         
            $inputRun = new \AppBundle\Entity\InputRun();
            $inputRun->setUri($inputs_array[$i]['inputRun']['value']);
            $inputRun->setContent($inputs_array[$i]['content']['value']);
            //$inputRun->setWorkflowRun($workflowRun);   
            $inputRun->setProcessRun($processRun);
            
            $input = new \AppBundle\Entity\Input();
            if (in_array('description', array_keys($inputs_array[$i])))
            {
                $input->setDescription($inputs_array[$i]['description']['value']);
            }
            $input->setLabel($inputs_array[$i]['label']['value']);
            $input->setUri($inputs_array[$i]['input']['value']);
            $inputRun->setInput($input);
            
            $inputs[] = $inputRun;
        }
        return $inputs; 
    }
    
    public function findOutputsRunByProcessRun($process_run_uri)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process_run_uri."> a wfprov:ProcessRun.
                ?outputRun prov:wasGeneratedBy <".$process_run_uri.">.
                ?outputRun tavernaprov:content ?content.
                ?outputRun wfprov:describedByParameter ?output.
                OPTIONAL { ?output dcterms:description ?description. }
                ?output rdfs:label ?label.
                ?output a wfdesc:Output.
                ?process  wfdesc:hasOutput ?output.                
            }}
            ";
        
        $outputs_array = $this->driver->getResults($query);
        
        $outputs = array();
        for ($i = 0; $i < count($outputs_array); $i++)
        {
            $processRun = new \AppBundle\Entity\ProcessRun();
            $processRun->setUri($process_run_uri);
            //$workflowRun = new \AppBundle\Entity\WorkflowRun();
            //$workflowRun->setUri($workflow_run_uri);
                         
            $outputRun = new \AppBundle\Entity\OutputRun();
            $outputRun->setUri($outputs_array[$i]['outputRun']['value']);
            $outputRun->setContent($outputs_array[$i]['content']['value']);
            //$outputRun->setWorkflowRun($workflowRun);   
            $outputRun->setProcessRun($processRun);
            
            $output = new \AppBundle\Entity\Output();
            if (in_array('description', array_keys($outputs_array[$i])))
            {
                $output->setDescription($outputs_array[$i]['description']['value']);
            }
            $output->setLabel($outputs_array[$i]['label']['value']);
            $output->setUri($outputs_array[$i]['output']['value']);
            $outputRun->setOutput($output);
            
            $outputs[] = $outputRun;
        }
        
        return $outputs;  
    }
    
    public function findOutputsRunByWorkflowRun($workflow_run_uri)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?outputRun wfprov:wasOutputFrom <".$workflow_run_uri.">.
                ?outputRun tavernaprov:content ?content.
                ?outputRun wfprov:describedByParameter ?output.
                OPTIONAL { ?output dcterms:description ?description. }
                ?output rdfs:label ?label.
                ?workflow  wfdesc:hasOutput ?output.
                <".$workflow_run_uri."> wfprov:describedByWorkflow ?workflow.
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
            
            $output = new \AppBundle\Entity\Output();
            if (in_array('description', array_keys($outputs_array[$i])))
            {
                $output->setDescription($outputs_array[$i]['description']['value']);
            }
            $output->setLabel($outputs_array[$i]['label']['value']);
            $output->setUri($outputs_array[$i]['output']['value']);
            $outputRun->setOutput($output);
            
            $outputs[] = $outputRun;
        }
        
        return $outputs;  
    }
    
    public function findInputsRunByWorkflowRun($workflow_run_uri)
    {
        // inputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow_run_uri."> wfprov:usedInput ?inputRun.
                ?inputRun tavernaprov:content ?content.
                ?inputRun wfprov:describedByParameter ?input.
                OPTIONAL { ?input dcterms:description ?description. }
                ?input rdfs:label ?label.
                ?workflow  wfdesc:hasInput ?input.
                <".$workflow_run_uri."> wfprov:describedByWorkflow ?workflow.
            }}
            ";
        
        $results_array = $this->driver->getResults($query);
        
        $inputs = array();
        for ($i = 0; $i < count($results_array); $i++)
        {
            $workflowRun = new \AppBundle\Entity\WorkflowRun();
            $workflowRun->setUri($workflow_run_uri);
                         
            $inputRun = new \AppBundle\Entity\InputRun();
            $inputRun->setUri($results_array[$i]['inputRun']['value']);
            $inputRun->setContent($results_array[$i]['content']['value']);
            $inputRun->setWorkflowRun($workflowRun);  
            
            $input = new \AppBundle\Entity\Input();
            if (in_array('description', array_keys($results_array[$i])))
            {
                $input->setDescription($results_array[$i]['description']['value']);
            }
            $input->setLabel($results_array[$i]['label']['value']);
            $input->setUri($results_array[$i]['input']['value']);
            $inputRun->setInput($input);
        
            $inputs[] = $inputRun;
        }
        
        return $inputs;  
    }
    
    
    public function findOutputDataByOutputRun($output_data_uri)
    {
        $query = "
            SELECT DISTINCT * WHERE 
            {
                GRAPH <".$this->driver->getDefaultGraph()."> 
                { 
                    <".$output_data_uri."> wfprov:describedByParameter ?output.
                    ?output a wfdesc:Output.
                    OPTIONAL { ?output dcterms:description ?description. }
                    ?output rdfs:label ?label.
                    <".$output_data_uri."> tavernaprov:content ?content.
                }
            }";
        
        $outputs_array = $this->driver->getResults($query);
        
        $outputs = array();
        for ($i = 0; $i < count($outputs_array); $i++)
        {
            $outputRun = new \AppBundle\Entity\OutputRun();
            $outputRun->setUri($output_data_uri);
            $outputRun->setContent($outputs_array[$i]['content']['value']);
            
            $output = new \AppBundle\Entity\Output();
            if (in_array('description', array_keys($outputs_array[$i])))
            {
                $output->setDescription($outputs_array[$i]['description']['value']);
            }
            $output->setLabel($outputs_array[$i]['label']['value']);
            $output->setUri($outputs_array[$i]['output']['value']);
            $outputRun->setOutput($output);
            
            $outputs[] = $outputRun;
        }
        
        return $outputs;
    }
    
    /**
     * Delete triples related to a workflowRun URI
     * @param type WorkflowRun
     */
    public function deleteWorkflowRun(\AppBundle\Entity\WorkflowRun $workflowRun)
    {
        $query = "
            DELETE FROM <".$this->driver->getDefaultGraph()."> {
                <".$workflowRun->getUri()."> ?property ?subject.                
            }
            WHERE
            {
                <".$workflowRun->getUri()."> ?property ?subject.  
            }
            ";  
        $this->driver->getResults($query);     
    } 
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph().">";        
        return $this->driver->getResults($query);                
    }
}
