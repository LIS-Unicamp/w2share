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
     * @Route("/query", name="query")
     */
    public function queryAction(Request $request)
    {
        $odbc = $this->get('app.odbc_driver'); 
        
        $query2 = "
        $this->prefix
        SELECT DISTINCT ?concept
        WHERE
        {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {[] a ?concept}}
        ";
    
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query2 . '\', NULL, 0)');
        $result = $query->_odbc_fetch_array2();
        
        return $this->render('provenance/hello.html.twig', array(
            'name' => "lucas",
            'result' => $result
        ));
    }
    
    /**
     * @Route("/workflows-run", name="workflows-run")
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
     * @Route("/workflow-run", name="workflow-run")
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
     * @Route("/workflow", name="workflow")
     */
    public function workflowAction(Request $request)
    {
        $workflow = $request->get('workflow');
        $odbc = $this->get('app.odbc_driver'); 
        
        $workflow = urldecode($workflow);
    
        $query1 = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?workflowRun wfprov:describedByWorkflow <$workflow>.
                ?workflowRun prov:endedAtTime ?endedAtTime.
                ?workflowRun prov:startedAtTime ?startedAtTime.
            }}
            ";
        $query2 = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <$workflow> dct:hasPart ?process.
                ?process a wfdesc:Process.
            }}
            ";

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        $result1 = $query->_odbc_fetch_array2();

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query2 . '\', NULL, 0)');   
        $result2 = $query->_odbc_fetch_array2();
        
        return $this->render('provenance/workflow.html.twig', array(
            'result1' => $result1,
            'result2' => $result2,
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/workflows", name="workflows")
     */
    public function workflowsAction(Request $request)
    {
        $odbc = $this->get('app.odbc_driver'); 
        
        $query1 = "
            $this->prefix
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?s rdf:type wfdesc:Workflow.            
            }}
        ";

        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        $result = $query->_odbc_fetch_array2();       
        
        return $this->render('provenance/workflows.html.twig', array(
            'result' => $result
        ));
    }
    
    /**
     * @Route("/process", name="process")
     */
    public function processAction(Request $request)
    {
        $process = $request->get('process');
        $odbc = $this->get('app.odbc_driver'); 
        
        $process = urldecode($process);
    
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

        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?processRun wfprov:usedInput ?usedInput.
                ?usedInput tavernaprov:content ?content.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $result2 = $query->_odbc_fetch_array2();

        $inputs = array();
        foreach($result2 as $row)
        {
            $inputs[$row['processRun']][] = $row['content'];
        }

        $query = "
            $this->prefix 
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                ?processRun wfprov:describedByProcess <".$process.">.
                ?processRun a wfprov:ProcessRun.
                ?output prov:wasGeneratedBy ?processRun.
                ?output tavernaprov:content ?content.
            }}
            ORDER BY  DESC(?startedAtTime)
            ";
        $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        $result3 = $query->_odbc_fetch_array2();

        $outputs = array();
        foreach($result3 as $row)
        {
            $outputs[$row['processRun']][] = $row['content'];
        }
        
        return $this->render('provenance/process.html.twig', array(
            'result' => $result1,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'process' => $process
        ));
    }
}


