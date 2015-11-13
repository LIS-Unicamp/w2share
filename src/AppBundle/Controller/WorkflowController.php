<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WorkflowController extends Controller
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
            
            return $this->redirect($this->generateUrl('workflow-edit', array('workflow_id' => $workflow->getId())));
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView()
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
            $odbc = $this->get('app.odbc_driver'); 
        
            $query1 = "
                $this->prefix  
                DELETE data FROM <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                    <".$workflow->getUri()."> rdf:type wfdesc:Workflow.                
                }
                ";  
            $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');
            
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
            
            $odbc = $this->get('app.odbc_driver'); 
        
            $query1 = "LOAD bif:concat (\"file://".$workflow->getProvenanceAbsolutePath()."\") INTO GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/>";

            echo $query1;
            $query = $odbc->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');   
        }
        
        return $this->render('workflow/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

