<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class QualityAnnotationController extends Controller
{                       
    /**
     * @Route("/annotation/quality-annotation-list", name="quality-annotation-list") 
     */
    public function qualityAnnotationListAction(Request $request)
    {         
        $model = $this->get('model.qualityannotation');
        $model_provenance = $this->get('model.provenance');
        $model_workflow = $this->get('model.workflow');
        
        $users = $model->findUsersWithQualityAnnotations();
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationFilterType($users), null, array(
            'action' => $this->generateUrl('quality-annotation-list'),
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
        
        $quality_annotation_array = array();
                        
        for ($i = 0; $i < count($query); $i++)
        {
            $qualityAnnotation = $query[$i];
            
            if ($qualityAnnotation->getWorkflow())
            {   
                $workflow = $model_workflow->findWorkflow($qualityAnnotation->getWorkflow()->getUri());
                
                $qualityAnnotation->setWorkflow($workflow);      
                                
            }
            elseif($qualityAnnotation->getProcessRun())
            {   
                $process_run = $model_provenance->findProcessRun($qualityAnnotation->getProcessRun()->getUri());  
                $process_uri = $process_run->getProcess()->getUri();
                $process = $model_workflow->findProcess($process_uri);
                $workflow_of_process = $model_workflow->findWorkflow($process->getWorkflow()->getUri());  
                $process->setWorkflow($workflow_of_process);
                
                $process_run->setProcess($process); 
                
                $qualityAnnotation->setProcessRun($process_run);
            }
            else
            {                 
                $output_data_run = $model_provenance->findOutputData($qualityAnnotation->getOutputRun()->getUri());
                
                $qualityAnnotation->setOutputRun($output_data_run);                
            }
            
            $quality_annotation_array[] = $qualityAnnotation;
            
        }
                        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $quality_annotation_array, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('quality-annotation/list.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));       
    }    
    
    /**
     * 
     * @Route("/annotation/quality-dimension/list/{element_uri}/{type}", name="element-quality-dimension-annotation-list")
     */
    public function qualityAnnotationsByElementListAction(Request $request, $element_uri, $type)
    {
        $element_uri = urldecode($element_uri);                                      
        
        $model_annotation = $this->get('model.qualityannotation');                
        
        $quality_annotations = $model_annotation->findQualityAnnotationsByElement($element_uri, $type);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $quality_annotations, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        ); 
              
        return $this->render('quality-annotation/quality-dimension-annotation-list.html.twig', array(
            'element_uri' => $element_uri,
            'pagination' => $pagination,
            'type' => $type
        ));         
    }
    
    /**
     * 
     * @Route("/annotation/quality-dimension/add/{element_uri}/{type}", name="element-quality-dimension-annotation-add")
     */
    public function addQualityAnnotationAction(Request $request, $element_uri, $type)
    {
        $element_uri = urldecode($element_uri);                      
        
        //Info from Quality dimension
        $model_qualitydimension = $this->get('model.qualitydimension'); 
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensionsForm();
        
        //Annotation quality dimension and value
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        $model_annotation = $this->get('model.qualityannotation');
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationType($quality_dimensions, $model_qualitydimension), $qualityAnnotation);
        
        $form->handleRequest($request);
        
        if ($form->isValid())
        {   
            $value = $form->get('value')->getData();
            $quality_dimension = $form->get('quality_dimension')->getData();
            $user = $this->getUser();
            
            $model_annotation->insertQualityAnnotation($element_uri, $type, $quality_dimension, $value, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Element annotated with a quality dimension!');            
        }                
              
        return $this->render('quality-annotation/quality-dimension-annotation-form.html.twig', array(
            'form' => $form->createView(),
            'element_uri' => $element_uri,
            'quality_dimensions' => $quality_dimensions,
            'qualityAnnotation' => $qualityAnnotation,
            'type' => $type
        ));         
    }
    
    /**
     * 
     * @Route("/annotation/quality-dimension/edit/{annotation_uri}/{type}", name="quality-dimension-annotation-edit")
     */
    public function editQualityAnnotationAction(Request $request, $annotation_uri, $type)
    {
        $model = $this->get('model.qualityannotation');      
        
        $uri = urldecode($annotation_uri);
        $qualityAnnotation = $model->findQualityAnnotationByURI($uri, $type);                
                
        //Info from Quality dimension
        $model_qualitydimension = $this->get('model.qualitydimension'); 
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensionsForm();
                
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationType($quality_dimensions, $model_qualitydimension), $qualityAnnotation);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {   
            $model->updateQualityAnnotation($qualityAnnotation);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality annotation edited!');
        }
        $element_uri = $qualityAnnotation->getElementUri();
        
        return $this->render('quality-annotation/quality-dimension-annotation-form.html.twig', array(
            'form' => $form->createView(),
            'element_uri' => $element_uri,
            'qualityAnnotation' => $qualityAnnotation,
            'quality_dimensions' => $quality_dimensions,
            'type' => $type
        ));        
    }
    
    public function elementInfoAction(Request $request, $element_uri, $type)
    {
        $model_workflow = $this->get('model.workflow');
        $model_provenance = $this->get('model.provenance');
        
        $workflow = new \AppBundle\Entity\Workflow();
        $process_run = new \AppBundle\Entity\ProcessRun();
        $output_data_run = new \AppBundle\Entity\OutputRun();                              
        
        switch ($type) 
        {
            case 'workflow': 
                $workflow = $model_workflow->findWorkflow($element_uri);
                break;
            case 'process_run':
                $process_run = $model_provenance->findProcessRun($element_uri);
                $process_uri = $process_run->getProcess()->getUri();
                $process = $model_workflow->findProcess($process_uri);
                $workflow_of_process = $model_workflow->findWorkflow($process->getWorkflow()->getUri());  
                $process->setWorkflow($workflow_of_process);
                $process_run->setProcess($process); 
                break;
            case 'output_run':
                $output_data_run = $model_provenance->findOutputData($element_uri);
                break;
        }
        
        return $this->render('quality-annotation/element-info.html.twig', array(
            'workflow' => $workflow,
            'process_run' => $process_run,
            'output_data_run' => $output_data_run,
            'type' => $type
        ));
    }
    
    /**
     * @Route("/annotation/quality-dimension/delete/{annotation_uri}/{type}", name="quality-dimension-annotation-delete")
     */
    
    public function removeAction(Request $request, $annotation_uri, $type)
    {   
        $model = $this->get('model.qualityannotation'); 
        
        $annotation_uri = urldecode($annotation_uri);
        $qualityAnnotation = $model->findQualityAnnotationByURI($annotation_uri, $type);        
        
        $element_uri = $qualityAnnotation->getElementUri();

        if ($qualityAnnotation)
        {                   
            $model->deleteQualityAnnotation($qualityAnnotation);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality annotation deleted!');            
        } 
        
        return $this->redirect($this->generateUrl('element-quality-dimension-annotation-list', array('type'=>$type, 'element_uri'=> urlencode($element_uri))));  
           
    }
    
    /**
     * @Route("/annotation/quality/reset", name="quality-annotation-reset")
     */
    public function qualityAnnotationResetAction(Request $request)
    {                
        $model = $this->get('model.qualityannotation');
        $model->clearGraphQualityAnnotation();
        
        return $this->redirect($this->generateUrl('homepage'));
    }                  
    
    /**
     * @Route("/annotation/quality-metric/add/{annotation_uri}/{type}", name="quality-metric-annotation-add")
     */
    public function addQualityMetricAnnotation(Request $request,  $annotation_uri, $type) 
    {
        $qualitymetric_uri = $request->get('quality_metric');
        $result = $request->get('result');
        
        $annotation_uri = urldecode($annotation_uri);
        $user = $this->getUser();
        
        $model_qualitymetric = $this->get('model.qualitymetric');
        $model_annnotation = $this->get('model.qualityannotation');
        
        $qualityMetric = $model_qualitymetric->findQualityMetric($qualitymetric_uri);

        $qualityAnnotation = $model_annnotation->findQualityAnnotationByURI($annotation_uri, $type);
        
        $element_uri = $qualityAnnotation->getElementUri();
        
        $model_annnotation->insertQualityMetricAnnotation($annotation_uri, $qualityMetric, $result, $user);
               
        return $this->redirect($this->generateUrl('element-quality-dimension-annotation-list', array(
                            'element_uri' => urlencode($element_uri),
                            'type' => $type
                        )));        
    }  
    
    /**
     * @Route("/annotation/quality-metric/edit/{element_uri}/{annotation_uri}/{type}", name="quality-metric-annotation-edit")
     */
    public function editQualityMetricAnnotationAction(Request $request, $element_uri, $annotation_uri, $type)
    {     
        $annotation_uri = urldecode($annotation_uri);
        $element_uri = urldecode($element_uri);
        
        $model_qualitymetric_annotation = $this->get('model.qualityannotation'); 
        
        $quality_metric_annotation = $model_qualitymetric_annotation->findQualityMetricAnnotation($annotation_uri);
        
        $form = $this->createForm(new \AppBundle\Form\QualityMetricAnnotationType(), $quality_metric_annotation);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) 
        {   
            $user = $this->getUser();
            $model_qualitymetric_annotation->updateQualityMetricAnnotation($quality_metric_annotation, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality metric annotation edited!')
            ;
        }
        
        return $this->render('quality-annotation/quality-metric-annotation-form.html.twig', array(
            'form' => $form->createView(),
            'qualityMetricAnnotation' => $quality_metric_annotation,
            'element_uri' => $element_uri,
            'type' => $type
        )); 
    }
    
    /**
     * @Route("/annotation/quality-metric/delete/{element_uri}/{annotation_uri}/{type}", name="quality-metric-annotation-delete")
     */
    public function removeQualityMetricAnnotationAction(Request $request, $element_uri, $annotation_uri, $type)
    {
        $annotation_uri = urldecode($annotation_uri);
        $element_uri = urldecode($element_uri);
        
        $model_qualitymetric_annotation = $this->get('model.qualityannotation'); 
        
        $quality_metric_annotation = $model_qualitymetric_annotation->findQualityMetricAnnotation($annotation_uri);

        if ($quality_metric_annotation)
        {              
            $model_qualitymetric_annotation->deleteQualityMetricAnnotation($quality_metric_annotation);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Metric annotation deleted!');            
        } 
        
        return $this->redirect($this->generateUrl('element-quality-dimension-annotation-list', array('type'=>$type, 'element_uri'=> urlencode($element_uri)))); 
    }
}