<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WorkflowController extends Controller
{
    /**
     * @Route("/workflows/list", name="workflow-list")
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
            
            return $this->redirect($this->generateUrl('workflow-edit', array('workflow_id' => $workflow->getId())));
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView()
        ));
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
            
            $odbc = $this->get('app.odbc_driver'); 
        
            $query1 = "LOAD bif:concat (\"file://home/lucas/Desktop/md-simulation.bundle/workflowrun.prov.ttl\") INTO GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/>";

            $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
            print_r($query);
            
            echo "resultado: ".$query;
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

