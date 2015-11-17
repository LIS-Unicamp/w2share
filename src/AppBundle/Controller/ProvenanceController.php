<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProvenanceController extends Controller
{               
    /**
     * @Route("/provenance/concepts", name="provenance-concepts")
     */
    public function conceptsAction(Request $request)
    {
        $concept = $request->get('concept');
        $query = $request->get('query');
        
        $model = $this->get('model.provenance'); 
        $result = $model->concepts();
        
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
        $concept = urldecode($request->get('concept'));
        $query = urldecode($request->get('query'));
        
        $model = $this->get('model.provenance'); 
        $result = $model->query($query, $concept);
                
        return $this->render('provenance/query-result.html.twig', array(
            'result' => $result
        ));
    }
    
    /**
     * @Route("/provenance/workflows-run", name="provenance-workflows-run")
     */
    public function workflowsRunAction(Request $request)
    {
        $model = $this->get('model.provenance'); 
        $result = $model->workflowsRun();
                
        return $this->render('provenance/workflows-run.html.twig', array(
            'result' => $result
        ));
    }
    
    /**
     * @Route("/provenance/workflow-run", name="provenance-workflow-run")
     */
    public function workflowRunAction(Request $request)
    {        
        $workflow_run = urldecode($request->get('workflow_run'));
        
        $model = $this->get('model.provenance'); 
        $result = $model->workflowRun($workflow_run);
        
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
        $workflow = urldecode($request->get('workflow'));
    
        $model = $this->get('model.provenance'); 

        // workflow run information
        $workflow_runs = $model->workflowRuns($workflow);
        $processes = $model->processes($workflow);
        $inputs = $model->workflowInputs($workflow);
        $outputs = $model->workflowOutputs($workflow);                                        
        
        return $this->render('provenance/workflow.html.twig', array(
            'result1' => $workflow_runs,
            'result2' => $processes,
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
        $workflow = urldecode($request->get('workflow'));
        $model = $this->get('model.provenance'); 
        $model->deleteWorkflow($workflow);
                    
        return $this->redirect($this->generateUrl('provenance-workflows'));
    }
    
    /**
     * @Route("/provenance/workflows", name="provenance-workflows")
     */
    public function workflowsAction(Request $request)
    {
        $model = $this->get('model.provenance'); 
        $result = $model->workflows();
                   
        return $this->render('provenance/workflows.html.twig', array(
            'result' => $result
        ));
    }
    
    
    /**
     * @Route("/provenance/process", name="provenance-process")
     */
    public function processAction(Request $request)
    {
        $process_uri = urldecode($request->get('process'));

        $model = $this->get('model.provenance'); 
           
        $result1 = $model->processRun($process_uri);
        $result2 = $model->processRunInputs($process_uri);
        $result3 = $model->processRunOutputs($process_uri); 
        $process_inputs = $model->processInputs($process_uri);
        $process_outputs = $model->processOutputs($process_uri);
        $process = $model->process($process_uri);

        $inputs = array();
        if ($result2 != '')
        {        
            foreach($result2 as $row)
            {
                $inputs[$row['processRun']][] = $row['content'];
            }
        }        

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
            'process' => $process,
            'process_uri' => $process_uri,
            'process_inputs' => $process_inputs,
            'process_outputs' => $process_outputs,
        ));
    }
}


