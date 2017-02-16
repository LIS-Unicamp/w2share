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
        
        $model_workflow = $this->get('model.workflow'); 
        if ($artefact == 'process')
        {
            $object = $model_workflow->findProcess($uri);
        }
                    
        return $this->render('annotation/list.html.twig', array(
            'object' => $object,
            'uri' => $uri,
            'annotations' => $annotations
        ));
    }  
        
    /**
     * 
     * @Route("/annotation/qualitydimension/workflow/{workflow_uri}", name="workflow-qualitydimension-annotation")
     */
    public function workflowQualityDimensionAnnotationAction(Request $request, $workflow_uri)
    {
        $workflow_uri = urldecode($workflow_uri);
        
        $model = $this->get('model.workflow');
                
        $workflow = $model->findWorkflow($workflow_uri);
    
        // workflow run information
        $processes = $model->findProcessesByWorkflow($workflow_uri);
        
        $inputs = $model->findWorkflowInputs($workflow_uri);
        $outputs = $model->findWorkflowOutputs($workflow_uri);
        
        //Info from Quality dimension
        $model_qualitydimension = $this->get('model.qualitydimension'); 
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensions();
        
        //Annotation quality dimension and value
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        $model_annotation = $this->get('model.annotation');
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationAddType($quality_dimensions), $qualityAnnotation,
                                  array(
                                  'action' => $this->generateUrl('workflow-qualitydimension-annotation', array('workflow_uri' => urlencode($workflow_uri))),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
        
        if ($form->isValid())
        {   
            $value = $form->get('value')->getData();
            $quality_dimension = $form->get('quality_dimension')->getData();
            $user = $this->getUser();
            //TO-DO: insertQualityAnnotation
            $model_annotation->insertQualityAnnotation($workflow, $quality_dimensions[$quality_dimension], $value, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow annotated with a quality dimension!'); 
        }
        $workflow_quality_annotations = $model_annotation->findQualityAnnotationByObject($workflow_uri);
        return $this->render('qualityflow/workflow-qualitydimension-annotation-form.html.twig', array(
            'form' => $form->createView(),
            'processes' => $processes,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'workflow' => $workflow,
            'workflow_uri' => $workflow_uri,
            'quality_dimensions' => $quality_dimensions,
            'workflow_quality_annotations' => $workflow_quality_annotations
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
    
}