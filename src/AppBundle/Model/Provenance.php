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
    
    public function concepts()
    {
        $query = "
            select distinct ?concept ?label where { GRAPH <".$this->driver->getDefaultGraph()."> {
            {
                [] a ?concept.
                OPTIONAL { ?concept skos:prefLabel ?label. }
                } union {
                                ?subject ?concept ?object.
                                OPTIONAL { ?concept skos:prefLabel ?label. }
                }
            }}
        ";
                        
        return $this->driver->getResults($query);
    }
    
    public function query($query, $concept)
    {
        $query2 = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                {
                    ?title a <".$concept.">
                    FILTER regex(?title, \"".$query."\", \"i\" )
                } union {
                    ?subject <".$concept."> ?title 
                    FILTER regex(?title, \"".$query."\", \"i\" )
                }
            }}
            ";

        return $this->driver->getResults($query2);
    }
    
    public function workflowsRun()
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflowRun rdf:type wfprov:WorkflowRun.
                ?workflowRun rdfs:label ?label.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
                ?workflowRun wfprov:describedByWorkflow ?workflow.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";

        return $this->driver->getResults($query);
    }
    
    /**
     * List of processes executed in a workflow run
     * @param type $workflow_run
     * @return type
     */
    public function workflowRun($workflow_run)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?processRun wfprov:describedByProcess ?process.
                ?processRun a wfprov:ProcessRun.
                ?processRun rdfs:label ?label.
                ?processRun prov:endedAtTime ?endedAtTime.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?processRun wfprov:wasPartOfWorkflowRun <".$workflow_run.">.
            }}
            ORDER BY  DESC(?startedAtTime)
        ";

        return $this->driver->getResults($query);
    }
    
    public function workflowRuns($workflow)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflowRun wfprov:describedByWorkflow <$workflow>.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function processes($workflow)
    {
        // process information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <$workflow> dct:hasPart ?process.
                ?process a wfdesc:Process.
                OPTIONAL { ?process rdfs:label ?label. }
                OPTIONAL { ?process dcterms:description ?description }
                OPTIONAL { ?process prov:specializationOf ?subworkflow. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function workflowInputs($workflow)
    {
        // inputs information
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow."> a wfdesc:Workflow;
                wfdesc:hasInput ?input.
                OPTIONAL { ?input rdfs:label ?label. }
                OPTIONAL { ?input dcterms:description ?description. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function workflowOutputs($workflow)
    {
        // outputs information
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$workflow."> a wfdesc:Workflow;
                wfdesc:hasOutput ?output.
                OPTIONAL { ?output rdfs:label ?label. }
                OPTIONAL { ?output dcterms:description ?description. }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function processRun($process)
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
    
    public function processRunInputs($process)
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
    
    public function processRunOutputs($process)
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
    
    public function processOutputs($process)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process."> a wfdesc:Process;
                wfdesc:hasOutput ?output.
                OPTIONAL { ?output rdfs:label ?label }
                OPTIONAL { ?output dcterms:description ?description }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function processInputs($process)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process."> a wfdesc:Process;
                wfdesc:hasInput ?input.
                OPTIONAL { ?input rdfs:label ?label }
                OPTIONAL { ?input dcterms:description ?description }
            }}
            ";
        
        return $this->driver->getResults($query);
    }
    
    public function process($process)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                <".$process."> a wfdesc:Process.
                ?workflow wfdesc:hasSubProcess <".$process.">.
                OPTIONAL { <".$process."> rdfs:label ?label }
                OPTIONAL { <".$process."> dcterms:description ?description }
                OPTIONAL { <".$process."> prov:specializationOf ?subworkflow. }
            }}
            ";
        
        return $this->driver->getSingleResult($query);
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph().">";        
        return $this->driver->getResults($query);                
    }
}
