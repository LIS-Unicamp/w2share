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
        $workflows_run = $model->findWorkflowsRunsByWorkflowOrAll();
                
        return $this->render('provenance/workflows-run.html.twig', array(
            'workflows_run' => $workflows_run
        ));
    }
    
    /**
     * @Route("/provenance/workflow-run/{workflow_run_uri}", name="provenance-workflow-run")
     */
    public function workflowRunAction(Request $request, $workflow_run_uri)
    {        
        $workflow_run_uri = urldecode($workflow_run_uri);
        
        $model = $this->get('model.provenance'); 
        $workflowRun = $model->findWorkflowRun($workflow_run_uri);        
        
        if (null === $workflowRun)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Workflow run not found!');
        }
        
        $processes = $model->findProcessesRunByWorkflowRun($workflow_run_uri);
        $outputs = $model->findOutputsRunByWorkflowRun($workflow_run_uri);
        $inputs = $model->findInputsRunByWorkflowRun($workflow_run_uri);
        
        $model_workflow = $this->get('model.workflow');
        $workflow = $model_workflow->findWorkflow($workflowRun->getWorkflow()->getUri());
        
        $workflowRun->setProcessesRun($processes);
        $workflowRun->setWorkflow($workflow);
        
        return $this->render('provenance/workflow-run.html.twig', array(
            'workflowRun' => $workflowRun,
            'inputs' => $inputs,
            'outputs' => $outputs
        ));
    }
    
    /**
     * @Route("/provenance/workflow-runs/{workflow_uri}", name="provenance-workflow")
     */
    public function workflowRunByWorkflowAction(Request $request, $workflow_uri)
    {       
        $workflow_uri = urldecode($workflow_uri);                
    
        $model_provenance = $this->get('model.provenance'); 
        $model_workflow = $this->get('model.workflow'); 

        // workflow run information
        $workflow = $model_workflow->findWorkflow($workflow_uri);
        
        if (null === $workflow)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Workflow not found!');
        }
        
        $workflow_runs = $model_provenance->findWorkflowsRunsByWorkflowOrAll($workflow_uri);                                               
        
        return $this->render('provenance/workflow.html.twig', array(
            'workflow_runs' => $workflow_runs,
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/provenance/workflow-run/delete/{workflow_run_uri}", name="provenance-workflow-run-delete")
     */
    public function workflowRunDeleteAction(Request $request, $workflow_run_uri)
    {        
        $workflow_run_uri = urldecode($workflow_run_uri);
        $model = $this->get('model.provenance');
        
        $workflowRun = $model->findWorkflowRun($workflow_run_uri);
        
        if ($workflowRun)
        {
            $model->deleteWorkflowRun($workflowRun);                       

            $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Workflow Run deleted!')
                ;
        }
        else
        {
            $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'Workflow Run does not exist!')
                ;
        }       
                    
        return $this->redirect($this->generateUrl('provenance-workflows-run'));
    }
                
    /**
     * @Route("/provenance/process-run/{process_run_uri}", name="provenance-process-run")
     */
    public function processRunAction(Request $request, $process_run_uri)
    {
        $process_run_uri = urldecode($process_run_uri);

        $model = $this->get('model.provenance'); 
           
        $processRun = $model->findProcessRun($process_run_uri);
        
        if (null === $processRun)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Process run not found!');
        }
        
        $inputsRun = $model->findInputsRunByProcessRun($process_run_uri);
        $outputsRun = $model->findOutputsRunByProcessRun($process_run_uri); 
        
        $process_uri = $processRun->getProcess()->getUri();
        
        $model_workflow = $this->get('model.workflow');
        $process = $model_workflow->findProcess($process_uri);
        $workflow = $model_workflow->findWorkflow($process->getWorkflow()->getUri());  
        $process->setWorkflow($workflow);
        
        $processRun->setProcess($process);        
        
        return $this->render('provenance/process-run.html.twig', array(
            'processRun' => $processRun,
            'inputsRun' => $inputsRun,
            'outputsRun' => $outputsRun,
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


