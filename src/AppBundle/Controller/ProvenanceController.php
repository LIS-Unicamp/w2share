<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProvenanceController extends Controller
{                                      
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
     * @Route("/provenance/workflow-run/{workflow_run_uri}", name="provenance-workflow-run")
     */
    public function workflowRunAction(Request $request, $workflow_run_uri)
    {        
        $workflow_run = urldecode($workflow_run_uri);
        
        $model = $this->get('model.provenance'); 
        $result = $model->workflowRun($workflow_run);
        
        return $this->render('provenance/workflow-run.html.twig', array(
            'result' => $result,
            'workflow_run' => $workflow_run
        ));
    }
    
    /**
     * @Route("/provenance/workflow-runs/{workflow_uri}", name="provenance-workflow")
     */
    public function workflowRunByworkflowAction(Request $request, $workflow_uri)
    {       
        $workflow_uri = urldecode($workflow_uri);                
    
        $model_provenance = $this->get('model.provenance'); 
        $model_workflow = $this->get('model.workflow'); 

        // workflow run information
        $workflow = $model_workflow->findWorkflow($workflow_uri);
        $workflow_runs = $model_provenance->workflowRuns($workflow_uri);                                               
        
        return $this->render('provenance/workflow.html.twig', array(
            'workflow_runs' => $workflow_runs,
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/provenance/workflow-run/{workflow_run_uri}/delete", name="provenance-workflow-run-delete")
     */
    public function workflowRunDeleteAction(Request $request, $workflow_uri)
    {        
        $workflow = urldecode($workflow_uri);
        $model = $this->get('model.provenance'); 
        $model->deleteWorkflowRun($workflow_uri);
                    
        return $this->redirect($this->generateUrl('provenance-workflows-run'));
    }
                
    /**
     * @Route("/provenance/process/{process_uri}", name="provenance-process")
     */
    public function processAction(Request $request, $process_uri)
    {
        $process_uri = urldecode($process_uri);

        $model = $this->get('model.provenance'); 
           
        $result1 = $model->processRun($process_uri);
        $result2 = $model->processRunInputs($process_uri);
        $result3 = $model->processRunOutputs($process_uri); 
        
        $model_workflow = $this->get('model.workflow');        
        $process_inputs = $model_workflow->findProcessInputs($process_uri);
        $process_outputs = $model_workflow->findProcessOutputs($process_uri);
        $process = $model_workflow->findProcess($process_uri);

        $inputs = array();
        if ($result2 != '')
        {        
            foreach($result2 as $row)
            {
                $inputs[$row['processRun']['value']][] = $row['content']['value'];
            }
        }        

        $outputs = array();        
        if (is_array($result3))
        {
            foreach($result3 as $row)
            {
                $outputs[$row['processRun']['value']][] = $row['content']['value'];
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
    
    /**
     * @Route("/provenance/reset", name="provenance-reset")
     */
    public function resetAction(Request $request)
    {                   
        $model_provenance = $this->get('model.provenance');         
        $model_provenance->clearGraph();                
                    
        return $this->redirect($this->generateUrl('provenance-workflows'));
    }  
}


