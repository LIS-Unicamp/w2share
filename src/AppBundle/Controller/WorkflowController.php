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
    public function listAction(Request $request)
    {         
        $model_workflow = $this->get('model.workflow'); 
        
        $workflows = $model_workflow->findAll();
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $workflows, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('workflow/list.html.twig', array(
            'pagination' => $pagination
        ));
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
            $workflow->preUpload();
            $workflow->upload();
            
            $model = $this->get('model.workflow');             
            $model->addWorkflow($workflow);
            
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
        $workflow_uri = urldecode($workflow_uri);
                
        $model = $this->get('model.workflow'); 
        $workflow = $model->findWorkflow($workflow_uri);

        if ($workflow)
        {
            $model->deleteWorkflow($workflow);                        

            $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Workflow deleted!')
                ;
        }
        else
        {
            $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'Workflow does not exist!')
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
        
        if (null === $workflow)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Workflow not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowType(), $workflow);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                     
            $workflow->preUpload();            
            $workflow->upload();
            
            $model = $this->get('model.workflow'); 
            $model->editWorkflow($workflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow edited!')
            ; 
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView(),
            'workflow' => $workflow
        ));
    }       
    
    /**
     * @Route("/workflow/process/{process_uri}", name="workflow-process")
     */
    public function workflowProcessAction(Request $request)
    {
        $process_uri = urldecode($request->get('process_uri'));

        $model = $this->get('model.workflow');             
        $process_inputs = $model->findProcessInputs($process_uri);
        $process_outputs = $model->findProcessOutputs($process_uri);
        $process = $model->findProcess($process_uri);
        $workflow = $model->findWorkflow($process->getWorkflow()->getUri());
        
        $model_provenance = $this->get('model.provenance');             
        $processRuns = $model_provenance->findProcessRunsByProcess($process_uri);
                
        return $this->render('workflow/process.html.twig', array(
            'process' => $process,
            'process_uri' => $process_uri,
            'process_inputs' => $process_inputs,
            'process_outputs' => $process_outputs,
            'workflow' => $workflow,
            'processRuns' => $processRuns
        ));
    }
    
    /**
     * @Route("/workflow/details/{workflow_uri}", name="workflow-details")
     */
    public function workflowAction(Request $request, $workflow_uri)
    {       
        $workflow_uri = urldecode($workflow_uri);
        
        $model = $this->get('model.workflow');                                   
        $workflow = $model->findWorkflow($workflow_uri);
        
        // workflow run information
        $processes = $model->findProcessesByWorkflow($workflow_uri);        
        $inputs = $model->findWorkflowInputs($workflow_uri);
        $outputs = $model->findWorkflowOutputs($workflow_uri);
        
        $model_provenance = $this->get('model.provenance');             
        $workflowRuns = $model_provenance->findWorkflowsRunsByWorkflowOrAll($workflow_uri);
        $workflow->setWorkflowRuns($workflowRuns);
        
        return $this->render('workflow/workflow.html.twig', array(
            'processes' => $processes,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'workflow' => $workflow,
            'workflow_uri' => $workflow_uri
        ));
    }
    
    /**
     * @Route("/workflow/reset", name="workflow-reset")
     */
    public function resetAction(Request $request)
    {                                   
        $model_workflow = $this->get('model.workflow'); 
        $model_workflow->clearGraph();
        $model_workflow->clearUploads();
                    
        return $this->redirect($this->generateUrl('workflows'));
    }   
}

