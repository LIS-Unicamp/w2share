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
    public function qualityAnnotationsByElementAction(Request $request, $element_uri, $type)
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
        $quality_dimensions = $model_qualitydimension->findAllQualityDimensions();
        
        //Annotation quality dimension and value
        $qualityAnnotation = new \AppBundle\Entity\QualityAnnotation();
        
        $model_annotation = $this->get('model.qualityannotation');
        
        $form = $this->createForm(new \AppBundle\Form\QualityAnnotationType($quality_dimensions), $qualityAnnotation,
                                  array(
                                  'action' => $this->generateUrl('element-quality-dimension-annotation-add', 
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
            
            $model_annotation->insertQualityAnnotation($element_uri, $type, $quality_dimensions[$quality_dimension], $value, $user);
            
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
        $model = $this->get('model.qualityannotation'); 
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
                $process = $model->findProcess($process_uri);
                $workflow_of_process = $model->findWorkflow($process->getWorkflow()->getUri());  
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
        
        $uri = urldecode($annotation_uri);
        $qualityAnnotation = $model->findQualityAnnotationByURI($uri, $type);                              
        
        if ($qualityAnnotation)
        {                   
            $model->deleteQualityAnnotation($qualityAnnotation, $type);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality annotation deleted!');
            
            return $this->redirect($this->generateUrl('element-quality-dimension-annotation', array('type'=>$type, 'element_uri'=>$qualityAnnotation->getElementUri())));            
        }                                                
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
        $model_qualitydimension= $this->get('model.qualitydimension');
        $model_annnotation = $this->get('model.qualityannotation');
        
        $qualityMetric = $model_qualitymetric->findQualityMetric($qualitymetric_uri);

        $quality_annotation = $model_annnotation->findQualityAnnotationByURI($annotation_uri, $type);
        
        $element_uri = "";
        if ($quality_annotation->getWorkflow()->getUri() != null)
        {
            $element_uri = $quality_annotation->getWorkflow()->getUri();
        }
        elseif ($quality_annotation->ProcessRun()->getUri() != null) 
        {
             $element_uri = $quality_annotation->ProcessRun()->getUri();
        }
        else
        {
            $element_uri = $quality_annotation->getOutputRun()->getUri();
        }
        
        $model_annnotation->insertQualityMetricAnnotation($annotation_uri, $qualityMetric, $result, $user);
        
        $quality_metric_annotation = $model_annnotation->findQualityMetricAnnotation($annotation_uri);
        
        //TODO: como recupero o $quality_metric_annotation para renderizar as informacoes no template?.
        // O redirect me envia a um controlador. 
        return $this->redirect($this->generateUrl('element-quality-dimension-annotation', array(
                            'element_uri' => urlencode($element_uri),
                            'type' => $type
                        )));        
    }    
}