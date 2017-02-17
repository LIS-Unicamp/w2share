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
     * @Route("/annotation/qualityannotations", name="quality-annotations") 
     */
    public function qualityAnnotationListAction(Request $request)
    {         
        $model = $this->get('model.annotation');
        
        $users = $model->findUsersWithQualityAnnotations();
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationFilterType($users), null, array(
            'action' => $this->generateUrl('quality-annotations'),
            'method' => 'GET'
        ));
        $form->handleRequest($request);             
        $user_uri = $form->get('user')->getViewData();
        
        if ($form->isSubmitted() && $user_uri) 
        {                                    
            $user = new \AppBundle\Entity\Person();
            $user->setUri($user_uri);
            $query = $model->findQualityAnnotationsByUser($user);
        }
        else
        {
            $query = $model->findAllQualityAnnotations();
        }
                        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('qualityflow/list-qualityannotations.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));       
    }
        
    /**
     * 
     * @Route("/annotation/qualitydimension/{workflow_uri}", name="element-qualitydimension-annotation")
     */
    public function addQualityAnnotationAction(Request $request, $workflow_uri)
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
                                  'action' => $this->generateUrl('element-qualitydimension-annotation', array('workflow_uri' => urlencode($workflow_uri))),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
        
        if ($form->isValid())
        {   
            $value = $form->get('value')->getData();
            $quality_dimension = $form->get('quality_dimension')->getData();
            $user = $this->getUser();
            
            $quality_annotation = $model_annotation->insertQualityAnnotation($workflow, $quality_dimensions[$quality_dimension], $value, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow annotated with a quality dimension!');
            
        }
       
        $query = $model_annotation->findQualityAnnotationByElement($workflow_uri);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('qualityflow/qualitydimension-annotation-form.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'processes' => $processes,
            'inputs' => $inputs,
            'outputs' => $outputs,
            'workflow' => $workflow,
            'workflow_uri' => $workflow_uri,
            'quality_dimensions' => $quality_dimensions
        )); 
        
    }
    
    /**
     * 
     * @Route("/annotation/qualitydimension/edit/{annotation_uri}", name="edit-qualitydimension-annotation")
     */
    public function editQualityAnnotationAction(Request $request, $annotation_uri)
    {
        $model = $this->get('model.annotation'); 
        
        //TO-DO
       /* $uri = urldecode($qualitydimension_uri);
        $qualityDimension = $model->findOneQualityDimension($uri);
              
        $form = $this->createForm(new \AppBundle\Form\QualityDimensionType(), $qualityDimension);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {           
            $model->updateQualityDimension($qualityDimension);
            
            //Remove qualityDimension from the session variable
            $qualityDimensions = $this->get('session')->get('qualityDimensions');
            $session_index = \AppBundle\Utils\Utils::findIndexSession($uri, $qualityDimensions);
            $qualityDimensions[$session_index] = $qualityDimension;
            
            $this->get('session')->set('qualityDimensions',$qualityDimensions);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality dimension edited!')
            ;
        }
        
        return $this->render('qualityflow/form.html.twig', array(
            'form' => $form->createView(),
            'qualityDimension' => $qualityDimension
        ));*/
        
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