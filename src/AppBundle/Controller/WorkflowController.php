<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WorkflowController extends Controller
{        
    /**
     * @Route("/workflow/list", name="workflow-list")
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
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowAddType(), $workflow, array(
            'action' => $this->generateUrl('workflow-add'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {       
            $workflow->upload();
            
            $model = $this->get('model.workflow');             
            $model->addWorkflow($workflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow added!')
            ; 
            return $this->redirect($this->generateUrl('workflows'));
        }
        
        return $this->render('workflow/form-add.html.twig', array(
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
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowEditType(), $workflow);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                     
            $workflow->upload();
            
            $model = $this->get('model.workflow'); 
            $model->editWorkflow($workflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow edited!')
            ; 
        }
        
        return $this->render('workflow/form-edit.html.twig', array(
            'form' => $form->createView(),
            'workflow' => $workflow
        ));
    }     
    
    /**
     * @Route("/workflow/download", options={"expose"=true}, name="workflow-download")
     */
    public function downloadWorkflowAction(Request $request)
    {      
        if ($request->get('hash'))
        {
            $hash = $request->get('hash');
        }
        else
        {
            $hash = $this->get('session')->get('hash');
        }
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $content = $converter->getWorkflowT2FlowFile();
        $file_path = $converter->getWorkflowT2FlowFilepath();
                
        $response = new \Symfony\Component\HttpFoundation\Response();   
        
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($file_path));
        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($file_path).'";');
        $response->headers->set('Content-length', filesize($file_path));

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response->setContent($content);
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
     * @Route("/workflow/process/edit/{process_uri}", name="workflow-process-edit")
     */
    public function editWorkflowProcessAction(Request $request, $process_uri)
    {             
        $process_uri = urldecode($process_uri);
        
        $model = $this->get('model.workflow');                                   
        $process = $model->findProcess($process_uri);
        
        if (null === $process)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Process not found!');
        }
        
        $workflow = $model->findWorkflow($process->getWorkflow()->getUri());
        $process->setWorkflow($workflow);
        
        $form = $this->createForm(new \AppBundle\Form\ProcessType(), $process);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                                 
            $dao = $this->get('dao.workflow'); 
            $dao->updateProcess($process);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Process edited!')
            ; 
        }
        
        return $this->render('workflow/form-process-edit.html.twig', array(
            'form' => $form->createView(),
            'process' => $process
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
     * @Route("/{type}/output/edit/{output_uri}", name="workflow-output-edit")
     */
    public function editWorkflowOutputAction(Request $request, $output_uri, $type)
    {             
        $output_uri = urldecode($output_uri);
        
        $model = $this->get('model.workflow');
        $dao = $this->get('dao.workflow');    
        
        if ($type == "workflow")
        {
            $output = $dao->findWorkflowOutput($output_uri);
            $workflow = $model->findWorkflow($output->getWorkflow()->getUri());
            $output->setWorkflow($workflow);
        }
        else
        {
            $output = $dao->findProcessOutput($output_uri);
            $process = $model->findProcess($output->getProcess()->getUri());
            $output->setProcess($process);
        }
        
        if (null === $output)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Output not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\OutputType(), $output);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                                 
            $dao->updateOutput($output);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Output edited!')
            ; 
        }
        
        return $this->render('workflow/form-output-edit.html.twig', array(
            'form' => $form->createView(),
            'output' => $output
        ));
    }
    
    /**
     * @Route("/{type}/input/edit/{input_uri}", name="workflow-input-edit")
     */
    public function editWorkflowInputAction(Request $request, $input_uri, $type)
    {             
        $input_uri = urldecode($input_uri);
        
        $model = $this->get('model.workflow');
        $dao = $this->get('dao.workflow');    
        
        if ($type == "workflow")
        {
            $input = $dao->findWorkflowInput($input_uri);
            $workflow = $model->findWorkflow($input->getWorkflow()->getUri());
            $input->setWorkflow($workflow);
        }
        else
        {
            $input = $dao->findProcessInput($input_uri);
            $process = $model->findProcess($input->getProcess()->getUri());
            $input->setProcess($process);
        }
        
        if (null === $input)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Input not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\InputType(), $input);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                                 
            $dao->updateInput($input);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Input edited!')
            ; 
        }
        
        return $this->render('workflow/form-input-edit.html.twig', array(
            'form' => $form->createView(),
            'input' => $input
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

