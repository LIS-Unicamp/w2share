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
        $result = $this->get('doctrine')
            ->getRepository('AppBundle:Workflow')->findAll();
        
        return $this->render('workflow/index.html.twig', array(
            'result' => $result
        ));
    }
    
    /**
     * @Route("/workflow/upload", name="workflow-upload")
     */
    public function uploadAction(Request $request)
    {        
        $em = $this->get('doctrine')->getManager();
        
        $workflow = new \AppBundle\Entity\Workflow();
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowUploadType($em), $workflow, array(
            'action' => $this->generateUrl('workflow-upload'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {  
            $em->persist($workflow);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow uploaded!')
            ; 
            
            $root_path = $this->get('kernel')->getRootDir();

            $model = $this->get('app.provenance'); 
            $model->workflow($workflow, $root_path);
            
            return $this->redirect($this->generateUrl('workflow-edit',array('workflow_id' => $workflow->getId())));
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView(),
            'workflow' => $workflow
        ));
    }
    
    /**
     * @Route("/workflow/delete/{workflow_id}", name="workflow-delete")
     */
    public function removeAction(Request $request, $workflow_id)
    {        
        $em = $this->get('doctrine')->getManager();                
        $workflow = $em->getRepository('AppBundle:Workflow')
                ->find($workflow_id);
                                
        if ($workflow)  
        {                          
            $model = $this->get('model.provenance'); 
            $model->deleteWorkflow($workflow->getUri());
                        
            $em->remove($workflow);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow deleted!')
            ;
        }
        
        return $this->redirect($this->generateUrl('workflows'));
    }
    
    /**
     * @Route("/workflow/edit/{workflow_id}", name="workflow-edit")
     */
    public function editAction(Request $request, $workflow_id)
    {        
        $em = $this->get('doctrine')->getManager();
                
        $workflow = $em->getRepository('AppBundle:Workflow')
                ->find($workflow_id);
        
        $form = $this->createForm(new \AppBundle\Form\WorkflowUploadType($em), $workflow);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {              
            $em->persist($workflow);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow edited!')
            ;
            
            $root_path = $this->get('kernel')->getRootDir();

            $model = $this->get('model.provenance'); 
            $model->workflow($workflow, $root_path);
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView(),
            'workflow' => $workflow
        ));
    }
}

