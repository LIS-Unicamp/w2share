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
    
    private $prefix = "
    prefix dc:  <http://purl.org/dc/elements/1.1/>
    prefix prov:  <http://www.w3.org/ns/prov#>
    prefix cnt:  <http://www.w3.org/2011/content#>
    prefix foaf:  <http://xmlns.com/foaf/0.1/>
    prefix dcmitype:  <http://purl.org/dc/dcmitype/>
    prefix wfprov:  <http://purl.org/wf4ever/wfprov#>
    prefix dcam:  <http://purl.org/dc/dcam/>
    prefix xml:  <http://www.w3.org/XML/1998/namespace>
    prefix vs:  <http://www.w3.org/2003/06/sw-vocab-status/ns#>
    prefix dcterms:  <http://purl.org/dc/terms/>
    prefix rdfs:  <http://www.w3.org/2000/01/rdf-schema#>
    prefix wot:  <http://xmlns.com/wot/0.1/>
    prefix wfdesc:  <http://purl.org/wf4ever/wfdesc#>
    prefix dct:  <http://purl.org/dc/terms/>
    prefix tavernaprov:  <http://ns.taverna.org.uk/2012/tavernaprov/>
    prefix owl:  <http://www.w3.org/2002/07/owl#>
    prefix xsd:  <http://www.w3.org/2001/XMLSchema#>
    prefix rdf:  <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    prefix skos:  <http://www.w3.org/2004/02/skos/core#>
    prefix scufl2:  <http://ns.taverna.org.uk/2010/scufl2#>";
    
    public function __construct($driver)
    {
        $this->driver = $driver;
    }
    
    
    public function storeWorkflow($workflow, $root_path)
    {
        $this->load($workflow->getProvenanceAbsolutePath());
        $this->load($workflow->getWfdescAbsolutePath());        

        $command = "ruby ".$root_path."/../src/AppBundle/Utils/script.rb ".$workflow->getWorkflowAbsolutePath()." ".$root_path."/../web/uploads/documents/".$workflow->getId().".png";            
        system($command);
    }
    
    protected function load($file_path)
    {
        //$query = "LOAD bif:concat (\"file://".$file_path."\") INTO GRAPH <".$this->driver->getDefaultGraph().">";
        
        //$load = "curl -T myfoaf.rdf http://demo.openlinksw.com/DAV/home/demo/rdf_sink/myfoaf.rdf -u demo:demo";
        
        //$this->driver->load($file_path);
        $query = "LOAD <http://".$this->driver->getDomain()."/phd-prototype/web/uploads/documents/".basename($file_path)."> INTO graph <".$this->driver->getDefaultGraph().">";
        $this->driver->getResults($query);
    }
    
    /**
     * Delete triples related to a workflow URI
     * @param type $workflow_uri
     */
    public function deleteWorkflow($workflow_uri)
    {
        $query = "
            $this->prefix  
            DELETE data FROM <".$this->driver->getDefaultGraph()."> {
                <".$workflow_uri."> rdf:type wfdesc:Workflow.                
            }
            ";  
        return $this->driver->getResults($query);
    }
    
    public function workflows()
    {
        $query = "
            $this->prefix
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflow rdf:type wfdesc:Workflow.
                OPTIONAL { ?workflow dcterms:description ?description. }
                OPTIONAL { ?workflow dcterms:title ?title. }
            }}
        ";
  
        return $this->driver->getResults($query);
    }
    
    public function concepts()
    {
        $query = "
            $this->prefix
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
            $this->prefix
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
        $this->prefix 
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
            $this->prefix 
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
            $this->prefix  
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
            $this->prefix  
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
            $this->prefix 
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
            $this->prefix 
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
            $this->prefix 
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
            $this->prefix 
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
            $this->prefix 
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
            $this->prefix 
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
            $this->prefix 
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
            $this->prefix 
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
