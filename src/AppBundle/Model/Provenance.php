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
        $query = "LOAD bif:concat (\"file://".$file_path."\") INTO GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/>";

        $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
    }
    
    /**
     * Delete triples related to a workflow URI
     * @param type $workflow_uri
     */
    public function deleteWorkflow($workflow_uri)
    {
        $query = "
            $this->prefix  
            DELETE data FROM <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <".$workflow_uri."> rdf:type wfdesc:Workflow.                
            }
            ";  
        $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');
    }
    
    public function workflows()
    {
        $query = "
            $this->prefix
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?workflow rdf:type wfdesc:Workflow.
                OPTIONAL { ?workflow dcterms:description ?description. }
                OPTIONAL { ?workflow dcterms:title ?title. }
            }}
        ";
  
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');
        
        return $query->_odbc_fetch_array2();
    }
    
    public function concepts()
    {
        $query = "
            $this->prefix
            select distinct ?concept ?label where { GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                [] a ?concept
                OPTIONAL { ?concept skos:prefLabel ?label. }
            }}
        ";
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');
        
        return $query->_odbc_fetch_array2();
    }
    
    public function query($query, $concept)
    {
        $query2 = "
            $this->prefix
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?title a <".$concept.">
                FILTER regex(?title, \"".$query."\", \"i\" )
            }}
            ";

        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query2 . '\', NULL, 0)');
        return $query->_odbc_fetch_array2();
    }
    
    public function workflowsRun()
    {
        $query1 = "
        $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?workflowRun rdf:type wfprov:WorkflowRun.
                ?workflowRun rdfs:label ?label.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
                ?workflowRun wfprov:describedByWorkflow ?workflow.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";

        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    /**
     * List of processes executed in a workflow run
     * @param type $workflow_run
     * @return type
     */
    public function workflowRun($workflow_run)
    {
        $query1 = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?processRun wfprov:describedByProcess ?process.
                ?processRun a wfprov:ProcessRun.
                ?processRun rdfs:label ?label.
                ?processRun prov:endedAtTime ?endedAtTime.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?processRun wfprov:wasPartOfWorkflowRun <".$workflow_run.">.
            }}
            ORDER BY  DESC(?startedAtTime)
        ";

        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function workflowRuns($workflow)
    {
        $query = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?workflowRun wfprov:describedByWorkflow <$workflow>.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function processes($workflow)
    {
        // process information
        $query = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <$workflow> dct:hasPart ?process.
                ?process a wfdesc:Process.
                OPTIONAL { ?process rdfs:label ?label. }
                OPTIONAL { ?process dcterms:description ?description }
                OPTIONAL { ?process prov:specializationOf ?subworkflow. }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function workflowInputs($workflow)
    {
        // inputs information
        $query = "
            $this->prefix 
            SELECT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <".$workflow."> a wfdesc:Workflow;
                wfdesc:hasInput ?input.
                OPTIONAL { ?input rdfs:label ?label. }
                OPTIONAL { ?input dcterms:description ?description. }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function workflowOutputs($workflow)
    {
        // outputs information
        $query = "
            $this->prefix 
            SELECT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <".$workflow."> a wfdesc:Workflow;
                wfdesc:hasOutput ?output.
                OPTIONAL { ?output rdfs:label ?label. }
                OPTIONAL { ?output dcterms:description ?description. }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function processRun($process)
    {
        // Process information
        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun prov:endedAtTime ?endedAtTime.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?processRun wfprov:wasPartOfWorkflowRun ?workflowRun.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function processRunInputs($process)
    {
        // inputs information
        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun wfprov:usedInput ?usedInput.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?usedInput tavernaprov:content ?content.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function processRunOutputs($process)
    {
        // outputs information
        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun prov:startedAtTime ?startedAtTime.
                ?output prov:wasGeneratedBy ?processRun.
                ?output tavernaprov:content ?content.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function processOutputs($process)
    {
        // outputs information
        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <".$process."> a wfdesc:Process;
                wfdesc:hasOutput ?output.
                OPTIONAL { ?output rdfs:label ?label }
                OPTIONAL { ?output dcterms:description ?description }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function processInputs($process)
    {
        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <".$process."> a wfdesc:Process;
                wfdesc:hasInput ?input.
                OPTIONAL { ?input rdfs:label ?label }
                OPTIONAL { ?input dcterms:description ?description }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function process($process)
    {
        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <".$process."> a wfdesc:Process.
                ?workflow wfdesc:hasSubProcess <".$process.">.
                OPTIONAL { <".$process."> rdfs:label ?label }
                OPTIONAL { <".$process."> dcterms:description ?description }
                OPTIONAL { <".$process."> prov:specializationOf ?subworkflow. }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2()[0];
    }
    
    public function clearGraph()
    {
        $query1 = "CLEAR GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/>";        
        $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');                  
    }
}
