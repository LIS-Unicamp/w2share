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
    
    //Ongoing:
    /**
     * @Route("/annotation/qualityannotationsCopy", name="quality-annotationsCopy") 
     */
    public function qualityAnnotationListActionCopy(Request $request)
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
        
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
                
        for ($i = 0; $i < count($query); $i++)
        {
            
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
     * @Route("/annotation/qualitydimension/add/{element_uri}/{type}", name="element-qualitydimension-annotation")
     */
    public function addQualityAnnotationAction(Request $request, $element_uri, $type)
    {
        $element_uri = urldecode($element_uri);
        
        $model = $this->get('model.workflow');
        $model_provenance = $this->get('model.provenance');
        
        $workflow = new \AppBundle\Entity\Workflow();
        $process_run = new \AppBundle\Entity\ProcessRun();
        $output_data_run = new \AppBundle\Entity\OutputRun();
        
        $process_uri = null;
        $process = null;
        
        switch ($type) 
        {
            case 'workflow': 
                $workflow = $model->findWorkflow($element_uri);
                break;
            case 'process_run':
                $process_run = $model_provenance->findProcessRun($element_uri);
                $process_uri = $process_run->getProcess()->getUri();
                $process = $model->findProcess($process_uri);
                $workflow_of_process = $model->findWorkflow($process->getWorkflow()->getUri());  
                $process->setWorkflow($workflow_of_process);
                $process_run->setProcess($process); 
                break;
            case 'output_run':
                $output_data_run = $model_provenance->findOutputDataByOutputRun($element_uri);
                break;
        }
        
        //Info from Quality dimension
        $model_qualitydimension = $this->get('model.qualitydimension'); 
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensions();
        
        //Annotation quality dimension and value
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        $model_annotation = $this->get('model.annotation');
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationType($quality_dimensions), $qualityAnnotation,
                                  array(
                                  'action' => $this->generateUrl('element-qualitydimension-annotation', 
                                                                  array('element_uri' => urlencode($element_uri),
                                                                        'type' => $type)),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
        
        if ($form->isValid())
        {   
            $value = $form->get('value')->getData();
            $quality_dimension = $form->get('quality_dimension')->getData();
            $user = $this->getUser();
            
            $quality_annotation = $model_annotation->insertQualityAnnotationToElement($element_uri, $type, $quality_dimensions[$quality_dimension], $value, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Element annotated with a quality dimension!');
            
        }
       
        $query = $model_annotation->findQualityAnnotationByElement($element_uri, $type);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('qualityflow/qualitydimension-annotation-form.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'workflow' => $workflow,
            'process_run' => $process_run,
            'output_data_run' => $output_data_run, 
            'element_uri' => $element_uri,
            'quality_dimensions' => $quality_dimensions,
            'qualityAnnotation' => $qualityAnnotation,
            'type' => $type
        )); 
        
    }
    
    /**
     * 
     * @Route("/annotation/qualitydimension/edit/{annotation_uri}/{type}", name="edit-qualitydimension-annotation")
     */
    public function editQualityAnnotationAction(Request $request, $annotation_uri, $type)
    {
        $model = $this->get('model.annotation'); 
        $model_workflow = $this->get('model.workflow');
        $model_provenance = $this->get('model.provenance');
        
        $workflow = new \AppBundle\Entity\Workflow();
        $process_run = new \AppBundle\Entity\ProcessRun();
        $output_data_run = new \AppBundle\Entity\OutputRun();
        
        $process_uri = null;
        $process = null;
        
        $uri = urldecode($annotation_uri);
        $qualityAnnotation = $model->findQualityAnnotationByURI($uri, $type);
        
        switch ($type) 
        {
            case 'workflow': 
                $element_uri = $qualityAnnotation->getWorkflow()->getUri();
                $workflow = $model_workflow->findWorkflow($element_uri);
                break;
            case 'process_run':
                $element_uri = $qualityAnnotation->getProcessRun()->getUri();
                $process_run = $model_provenance->findProcessRun($element_uri);
                $process_uri = $process_run->getProcess()->getUri();
                $process = $model_workflow->findProcess($process_uri);
                $workflow_of_process = $model_workflow->findWorkflow($process->getWorkflow()->getUri());  
                $process->setWorkflow($workflow_of_process);
                $process_run->setProcess($process); 
                break;
            case 'output_run':
                $element_uri = $qualityAnnotation->getOutputRun()->getUri();
                $output_data_run = $model_provenance->findOutputDataByOutputRun($element_uri);
                break;
        }
                
        //Info from Quality dimension
        $model_qualitydimension = $this->get('model.qualitydimension'); 
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensions();
                
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationType($quality_dimensions), $qualityAnnotation);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {   
            $model->updateQualityAnnotation($qualityAnnotation, $type);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality annotation edited!');
        }
        
        $query = $model->findQualityAnnotationByElement($element_uri, $type);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
                
        
        return $this->render('qualityflow/qualitydimension-annotation-form.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'workflow' => $workflow,
            'process_run' => $process_run,
            'output_data_run' => $output_data_run,
            'qualityAnnotation' => $qualityAnnotation,
            'qualityDimensions' => $quality_dimensions,
            'type' => $type
        ));
        
    }
    
    /**
     * @Route("/annotation/qualitydimension/delete/{annotation_uri}/{type}", name="delete-qualitydimension-annotation")
     */
    
    public function removeAction(Request $request, $annotation_uri, $type)
    {   
        $model = $this->get('model.annotation'); 
        $model_workflow = $this->get('model.workflow');
        $model_provenance = $this->get('model.provenance');
        
        $workflow = new \AppBundle\Entity\Workflow();
        $process_run = new \AppBundle\Entity\ProcessRun();
        $output_data_run = new \AppBundle\Entity\OutputRun();
        
        $process_uri = null;
        $process = null;
        
        //Info from Quality dimensions
        $model_qualitydimension = $this->get('model.qualitydimension'); 
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensions();
        
        $uri = urldecode($annotation_uri);
        $qualityAnnotation = $model->findQualityAnnotationByURI($uri, $type);        
              
        switch ($type) 
        {
            case 'workflow': 
                $element_uri = $qualityAnnotation->getWorkflow()->getUri();
                $workflow = $model_workflow->findWorkflow($element_uri);
                break;
            case 'process_run':
                $element_uri = $qualityAnnotation->getProcessRun()->getUri();
                $process_run = $model_provenance->findProcessRun($element_uri);
                $process_uri = $process_run->getProcess()->getUri();
                $process = $model_workflow->findProcess($process_uri);
                $workflow_of_process = $model_workflow->findWorkflow($process->getWorkflow()->getUri());  
                $process->setWorkflow($workflow_of_process);
                $process_run->setProcess($process); 
                break;
            case 'output_run':
                $element_uri = $qualityAnnotation->getOutputRun()->getUri();
                $output_data_run = $model_provenance->findOutputDataByOutputRun($element_uri);
                break;
        }
        
        if ($qualityAnnotation)
        {                   
            $model->deleteQualityAnnotation($qualityAnnotation, $type);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality annotation deleted!');
            
            $qualityAnnotation = $model->findQualityAnnotationByURI($uri, $type);
        }
        
        if ($qualityAnnotation == null)
        {
            $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        }
                
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationType($quality_dimensions), $qualityAnnotation,
                                  array(
                                  'action' => $this->generateUrl('element-qualitydimension-annotation', 
                                                                  array('element_uri' => urlencode($element_uri),
                                                                        'type' => $type))));
        
        $form->handleRequest($request);
        
        $query = $model->findQualityAnnotationByElement($element_uri, $type);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('qualityflow/qualitydimension-annotation-form.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'workflow' => $workflow,
            'process_run' => $process_run,
            'output_data_run' => $output_data_run,
            'qualityAnnotation' => $qualityAnnotation,
            'type' => $type
        ));
    }
    
    /**
     * @Route("/annotation/quality/reset", name="quality-annotation-reset")
     */
    public function qualityAnnotationResetAction(Request $request)
    {                
        $model = $this->get('model.annotation');
        $model->clearGraphQualityAnnotation();
        
        return $this->redirect($this->generateUrl('homepage'));
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
