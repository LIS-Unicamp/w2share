<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnnotationController extends Controller
{                
    /**
     * @Route("/annotation/form", name="annotatation-form")
     */
    public function annotateAction(Request $request)
    {        
        $uri = urldecode($request->get('uri'));
        $artefact = $request->get('artefact');
        
        $model = $this->get('model.annotation'); 
        $ontology = $model->ontology();
        
        $object = $request->get('object');
        $subject = $request->get('subject');
        $property = $request->get('property');
        
        if ($object)
        {
            $model->insertAnnotation($subject, $property, $object);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Annotation created!')
            ; 
        }
                    
        return $this->render('annotation/form.html.twig', array(
            'object' => $object,
            'ontology' => $ontology,
            'subject' => $subject,
            'property' => $property
        ));
    }    
    
    /**
     * @Route("/annotatation/list", name="annotatation-list")
     */
    public function annotatationListAction(Request $request)
    {        
        $uri = urldecode($request->get('uri'));
        $artefact = $request->get('artefact');
        $object = $request->get('object');

        $model = $this->get('model.annotation'); 
        $annotations = $model->listAnnotations($uri);
        
        $model_provenance = $this->get('model.provenance'); 
        if ($artefact == 'process')
        {
            $object = $model_provenance->process($uri);
        }
                    
        return $this->render('annotation/list.html.twig', array(
            'object' => $object,
            'uri' => $uri,
            'annotations' => $annotations
        ));
    }  
    
    /**
     * @Route("/annotatation/reset", name="annotation-reset")
     */
    public function annotatationResetAction(Request $request)
    {                
        $model = $this->get('model.annotation'); 
        $model->clearGraph();
        
        return $this->redirect($this->generateUrl('homepage'));
    }  
    
    /**
     * @Route("/annotation/qualitydimension", name="annotatation-qualitydimension")
     */
    public function annotatationQualityDimensionAction(Request $request)
    {   
        //Retrieve all workflows
        $workflow_run = urldecode($request->get('workflow_run'));
        
        $model_workflow_run = $this->get('model.provenance'); 
        $workflows = $model_workflow_run->workflowRun($workflow_run);
                
        /*Possibilidades:
         * 1. Workflow anotado com uma ou mais dimenssões de qualidade.
         * 2. Idem com os processos.
         * 3. Idem com os resultados  
        */
        $qualityannotation = new \AppBundle\Entity\QualityAnnotation();
        $model = $this->get('model.annotation');
        
        $value = $request->get('value');
        $name = $request->get('name');
        
        $em = $this->get('doctrine')->getManager();
        //$qualitydimension = $em->getRepository('AppBundle:QualityDimension')
          //                          ->findOneBy(array( 'name'=> $name ) );
      
       
        //Just for test - remover depois
        $qualitydimension = new \AppBundle\Entity\QualityDimension();
        $qualitydimension->setName('teste');
        $qualitydimension->setDescription('description');
        $qualitydimension->setValueType('type');
        
        // Load all the workflows
        $workflows = $this->get('doctrine')
            ->getRepository('AppBundle:Workflow')->findAll();
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationAddType($em), $qualityannotation,
                                  array(
                                  'action' => $this->generateUrl('annotatation-qualitydimension'),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
        
        if ($form->isValid())
        {
            $model->insertQualityAnnotation($qualitydimension, $value);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Annotation created!'); 
        }
        
        //TO-DO verificar onde irá redirecionar
        return $this->render('qualityflow/annotation-form.html.twig', array(
            'form' => $form->createView(),
            'name' => $name,
            'value' => $value,
            'qualitydimension' => $qualitydimension,
            'workflows' => $workflows
        ));
    }
    
}