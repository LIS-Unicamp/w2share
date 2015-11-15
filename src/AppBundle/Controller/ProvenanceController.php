<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProvenanceController extends Controller
{
    var $prefix = "
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
    
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {                
        return $this->render('provenance/index.html.twig', array(
            
        ));
    }
    
    /**
     * @Route("/provenance/concepts", name="provenance-concepts")
     */
    public function conceptsAction(Request $request)
    {
        $odbc = $this->get('app.odbc_driver'); 
        $concept = $request->get('concept');
        $query = $request->get('query');
        
        $query2 = "
        $this->prefix
        select distinct ?concept ?label where { GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
            [] a ?concept
            OPTIONAL { ?concept skos:prefLabel ?label. }
        }}
        ";
        $query2 = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query2 . '\', NULL, 0)');
        $result = $query2->_odbc_fetch_array2();
        
        return $this->render('provenance/concepts-select.html.twig', array(
            'concept' => $concept,
            'query' => $query,
            'result' => $result
        ));
    }
    
    /**
     * @Route("/provenance/query", name="provenance-query")
     */
    public function queryAction(Request $request)
    {
        $odbc = $this->get('app.odbc_driver'); 
        $concept = urldecode($request->get('concept'));
        $query = urldecode($request->get('query'));
        
        $query2 = "
        $this->prefix
        SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
            ?title a <".$concept.">
            FILTER regex(?title, \"".$query."\", \"i\" )
        }}
        ";

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query2 . '\', NULL, 0)');
        $result = $query->_odbc_fetch_array2();
        
        return $this->render('provenance/query-result.html.twig', array(
            'name' => "lucas",
            'result' => $result
        ));
    }
    
    /**
     * @Route("/provenance/workflows-run", name="provenance-workflows-run")
     */
    public function workflowsRunAction(Request $request)
    {
        $odbc = $this->get('app.odbc_driver'); 
        
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

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        $result = $query->_odbc_fetch_array2();
        
        return $this->render('provenance/workflows-run.html.twig', array(
            'result' => $result
        ));
    }
    
    /**
     * @Route("/provenance/workflow-run", name="provenance-workflow-run")
     */
    public function workflowRunAction(Request $request)
    {
        $workflow_run = $request->get('workflow_run');
        $odbc = $this->get('app.odbc_driver'); 
        
        $workflow_run = urldecode($workflow_run);
    
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

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        $result = $query->_odbc_fetch_array2();
        
        return $this->render('provenance/workflow-run.html.twig', array(
            'result' => $result,
            'workflow_run' => $workflow_run
        ));
    }
    
    /**
     * @Route("/provenance/workflow", name="provenance-workflow")
     */
    public function workflowAction(Request $request)
    {
        $workflow = $request->get('workflow');
        $odbc = $this->get('app.odbc_driver'); 
        
        $workflow = urldecode($workflow);
    
        // workflow run information
        $query1 = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?workflowRun wfprov:describedByWorkflow <$workflow>.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
            }}
            ";
        
        // process information
        $query2 = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <$workflow> dct:hasPart ?process.
                ?process a wfdesc:Process.
                OPTIONAL { ?process rdfs:label ?label. }
            }}
            ";
        
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
        $query3 = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $inputs = $query3->_odbc_fetch_array2();        

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
        
        $query4 = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $outputs = $query4->_odbc_fetch_array2();        

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        $result1 = $query->_odbc_fetch_array2();

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query2 . '\', NULL, 0)');   
        $result2 = $query->_odbc_fetch_array2();
        
        return $this->render('provenance/workflow.html.twig', array(
            'result1' => $result1,
            'result2' => $result2,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/provenance/workflow/{workflow}/delete", name="provenance-workflow-delete")
     */
    public function workflowDeleteAction(Request $request)
    {
        $workflow = $request->get('workflow');
        $odbc = $this->get('app.odbc_driver'); 
        
        $workflow = urldecode($workflow);
    
        $query1 = "
            $this->prefix  
            DELETE data FROM <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <$workflow> rdf:type wfdesc:Workflow.                
            }
            ";  
        
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        
        return $this->redirect($this->generateUrl('provenance-workflows'));
    }
    
    /**
     * @Route("/provenance/workflows", name="provenance-workflows")
     */
    public function workflowsAction(Request $request)
    {
        $odbc = $this->get('app.odbc_driver'); 
        
        $query1 = "
            $this->prefix
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?workflow rdf:type wfdesc:Workflow.
                OPTIONAL { ?workflow dcterms:description ?description. }
                OPTIONAL { ?workflow dcterms:title ?title. }
            }}
        ";

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        $result = $query->_odbc_fetch_array2();       
        
        return $this->render('provenance/workflows.html.twig', array(
            'result' => $result
        ));
    }
    
    
    /**
     * @Route("/provenance/process", name="provenance-process")
     */
    public function processAction(Request $request)
    {
        $process = $request->get('process');
        $odbc = $this->get('app.odbc_driver'); 
        
        $process = urldecode($process);
    
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
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $result1 = $query->_odbc_fetch_array2();

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
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $result2 = $query->_odbc_fetch_array2();

        $inputs = array();
        if ($result2 != '')
        {        
            foreach($result2 as $row)
            {
                $inputs[$row['processRun']][] = $row['content'];
            }
        }

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
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $result3 = $query->_odbc_fetch_array2();

        $outputs = array();
        
        if (is_array($result3))
        {
            foreach($result3 as $row)
            {
                $outputs[$row['processRun']][] = $row['content'];
            }
        }
        
        return $this->render('provenance/process.html.twig', array(
            'result' => $result1,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'process' => $process
        ));
    }
}


