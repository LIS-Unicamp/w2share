<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of QualityMetricController
 *
 * @author joana
 */
class QualityMetricController extends Controller
{            
    /**
     * 
     * @Route("/qualitymetric/add/{qualitydimension_uri}", name="add-qualitymetric")
     */
    public function addQualityMetricAction(Request $request, $qualitydimension_uri)
    {   
        $qualitydimension_uri = urldecode($qualitydimension_uri);
        
        $model_qualitydimension = $this->get('model.qualitydimension');
        $model_qualitymetric = $this->get('model.qualitymetric'); 
        
        $quality_dimension = $model_qualitydimension->findOneQualityDimension($qualitydimension_uri);
        
        $qualityMetric = new \AppBundle\Entity\QualityMetric(); 

        $form = $this->createForm(new \AppBundle\Form\QualityMetricType(), $qualityMetric,
                                          array(
                                          'action' => $this->generateUrl('add-qualitymetric', 
                                                                        array('qualitydimension_uri' => urlencode($qualitydimension_uri))),
                                          'method' => 'POST'
                                          ));
        
        $form->handleRequest($request);
        
        if ($form->isValid())
        {   
            $metric = $form->get('metric')->getData();
            $description = $form->get('description')->getData();
            $user = $this->getUser();
            
            $quality_metric = $model_qualitymetric->insertQualityMetricToQualityDimension($quality_dimension, $metric, $description, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality metric added!');
        }
        
       $query = $model_qualitymetric->findQualityMetricByDimension($qualitydimension_uri);
       
       $paginator  = $this->get('knp_paginator');
       $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        10 /*limit per page*/
       );
        
        return $this->render('qualityflow/quality-metrics-form.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView(),
            'quality_dimension' => $quality_dimension,
            'qualityMetric' => $qualityMetric
        )); 
        
    }
    
    /**
     * @Route("/qualitymetric/edit/{qualitymetric_uri}", name="edit-qualitymetric")
     */
    public function editQualityMetricAction(Request $request, $qualitymetric_uri)
    {     
        $qualitymetric_uri = urldecode($qualitymetric_uri);
        $model_qualitydimension = $this->get('model.qualitydimension');
        $model_qualitymetric = $this->get('model.qualitymetric'); 
        
        $quality_metric = $model_qualitymetric->findQualityMetric($qualitymetric_uri);
        
        $quality_dimension = $model_qualitydimension->findOneQualityDimension($quality_metric->getQualityDimension()->getUri());
        
        $form = $this->createForm(new \AppBundle\Form\QualityMetricType(), $quality_metric);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) 
        {   
            $user = $this->getUser();
            $model_qualitymetric->updateQualityMetric($quality_metric, $user);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality metric edited!')
            ;
        }
        
        $query = $model_qualitymetric->findQualityMetricByDimension($quality_dimension->getUri());
       
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
         $query, /* query NOT result */
         $request->query->getInt('page', 1), /*page number*/
         10 /*limit per page*/
        );
        
        return $this->render('qualityflow/quality-metrics-form.html.twig', array(
            'pagination'=> $pagination,
            'form' => $form->createView(),
            'qualityMetric' => $quality_metric,
            'quality_dimension' => $quality_dimension
        ));
    }
    
    /**
     * @Route("/qualitymetric/delete/{qualitymetric_uri}", name="delete-qualitymetric")
     */
    public function removeQualityMetricAction(Request $request, $qualitymetric_uri)
    {   
        $qualitymetric_uri = urldecode($qualitymetric_uri);
        $model_qualitydimension = $this->get('model.qualitydimension');
        $model_qualitymetric = $this->get('model.qualitymetric');
        
        $quality_metric = $model_qualitymetric->findQualityMetric($qualitymetric_uri);
        
        $quality_dimension = $model_qualitydimension->findOneQualityDimension($quality_metric->getQualityDimension()->getUri());
            
        if ($quality_metric)
        {                  
            $model_qualitymetric->deleteQualityMetric($quality_metric);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality metric deleted!');
        }
                
        return $this->redirect($this->generateUrl('add-qualitymetric', array('qualitydimension_uri' => urlencode($quality_dimension->getUri()))));
        
    }
    
    /**
     * @Route("/qualitymetrics", name="qualitymetrics")
     */
    public function listQualityMetrics(Request $request) 
    {
        
        $model_qualitymetric = $this->get('model.qualitymetric');
        $model_qualitydimension= $this->get('model.qualitydimension');
        
        $users = $model_qualitymetric->findUsersWithQualityMetrics();

        $form = $this->createForm(new \AppBundle\Form\QualityMetricFilterType($users), null, array(
            'action' => $this->generateUrl('qualitymetrics'),
            'method' => 'GET'
        ));
        
        $form->handleRequest($request);             
        $user_uri = $form->get('user')->getViewData();

        if ($form->isSubmitted() && $user_uri) 
        {                                    
            $user = new \AppBundle\Entity\Person();
            $user->setUri($user_uri);
            $query = $model_qualitymetric->findQualityMetricsByUser($user);
        }
        else
        {
            $query = $model_qualitymetric->findAllQualityMetrics();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('qualityflow/list-qualitymetrics.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));      
        
    }
    
    /**
     * @Route("/qualitymetric/reset", name="qualitymetric-reset")
     */
    public function resetQualityMetricAction(Request $request)
    {                   
        $model_qualitymetric = $this->get('model.qualitymetric');         
        $model_qualitymetric->clearGraph();          
                    
        return $this->redirect($this->generateUrl('qualitymetrics'));
    }
    
}
