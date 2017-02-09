<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WorkflowController extends Controller
{        
    /**
     * @Route("/workflows", name="workflows")
     */
    public function indexAction(Request $request)
    {         
        $model_workflow = $this->get('model.workflow'); 
        
        $result = $model_workflow->findAll();
        
        return $this->render('workflow/index.html.twig', array(
            'result' => $result
        ));
    }
    
     /**
     * @Route("/workflow/reset", name="workflow-reset")
     */
    public function resetAction(Request $request)
    {                                   
        $root_path = $this->get('kernel')->getRootDir();

        $model_workflow = $this->get('model.workflow'); 
        $model_workflow->clearGraph();
        $model_workflow->clearUploads($root_path);
                    
        return $this->redirect($this->generateUrl('workflows'));
    }     
    
    /**
     * @Route("/workflow/add", name="workflow-add")
     */
    public function addWorkflowAction(Request $request)
    {                
        $workflow = new \AppBundle\Entity\Workflow();
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowType(), $workflow, array(
            'action' => $this->generateUrl('workflow-add'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {             
            $workflow->upload();
            
            $model = $this->get('model.workflow'); 
            $model->saveWorkflow($workflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow added!')
            ; 
            return $this->redirect($this->generateUrl('workflows'));
            //return $this->redirect($this->generateUrl('workflow-edit',array('workflow_uri' => urlencode($workflow->getUri()))));
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView(),
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/workflow/delete/{workflow_uri}", name="workflow-delete")
     */
    public function removeWorkflowAction(Request $request, $workflow_uri)
    {                
                                
        $model = $this->get('model.workflow'); 
        $model->deleteWorkflow($workflow->getUri());                        

        if (true)
        {
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow deleted!')
            ;
        }
        
        return $this->redirect($this->generateUrl('workflows'));
    }
    
    /**
     * @Route("/workflow/edit/{workflow_uri}", name="workflow-edit")
     */
    public function editWorkflowAction(Request $request, $workflow_uri)
    {             
        $workflow_uri = urldecode($workflow_uri);
        
        $model = $this->get('model.workflow');                                   
        $workflow = $model->findWorkflow($workflow_uri);
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowType(), $workflow);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {              
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow edited!')
            ;
            
            $root_path = $this->get('kernel')->getRootDir();

            $model = $this->get('model.workflow'); 
            $model->editWorkflow($workflow, $root_path);
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView(),
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/workflow/{workflow_uri}", name="workflow-details")
     */
    public function workflowAction(Request $request, $workflow_uri)
    {       
        $workflow_uri = urldecode($workflow_uri);
        
        $model = $this->get('model.workflow');                                   
        $workflow = $model->findWorkflow($workflow_uri);

        // workflow run information
        $processes = $model->processes($workflow_uri);
        
        $model_provenance = $this->get('model.provenance'); 
        $inputs = $model_provenance->workflowInputs($workflow_uri);
        $outputs = $model_provenance->workflowOutputs($workflow_uri);                                        
        
        return $this->render('workflow/workflow.html.twig', array(
            'processes' => $processes,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'workflow' => $workflow,
            'workflow_uri' => $workflow_uri
        ));
    }
    
    /**
     * @Route("/workflow/process", name="workflow-process")
     */
    public function workflowProcessAction(Request $request)
    {
        $process_uri = urldecode($request->get('process'));

        $model = $this->get('model.provenance');             
        $process_inputs = $model->processInputs($process_uri);
        $process_outputs = $model->processOutputs($process_uri);
        $process = $model->process($process_uri);
                
        return $this->render('workflow/process.html.twig', array(
            'process' => $process,
            'process_uri' => $process_uri,
            'process_inputs' => $process_inputs,
            'process_outputs' => $process_outputs,
        ));
    }
}

