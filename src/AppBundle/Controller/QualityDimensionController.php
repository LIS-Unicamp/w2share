<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of QualityDimensionController
 *
 * @author joana
 */
class QualityDimensionController extends Controller{
    
    /**
     * @Route("/qualitydimensions", name="qualitydimensions")
     */
    public function indexAction(Request $request)
    {         
        $model = $this->get('model.qualitydimension');
        $users = $model->findUsersWithQualityDimensions();

        $form = $this->createForm(new \AppBundle\Form\QualityDimensionFilterType($users), null, array(
            'action' => $this->generateUrl('qualitydimensions'),
            'method' => 'GET'
        ));
        $form->handleRequest($request);             
        $user_uri = $form->get('user')->getViewData();
        
        if ($form->isSubmitted() && $user_uri) 
        {                                    
            $user = new \AppBundle\Entity\Person();
            $user->setUri($user_uri);
            $query = $model->findQualityDimensionsByUser($user);
        }
        else
        {
            $query = $model->findAllQualityDimensions();
        }
                        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('qualityflow/list-qualitydimension.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));       
    }

    /**
     * @Route("/qualitydimension/add", name="qualitydimension-add")
     */
    public function addAction(Request $request) {
        
        $model = $this->get('model.qualitydimension'); 
        
        $qualityDimension = new \AppBundle\Entity\QualityDimension();
        $form = $this->createForm(new \AppBundle\Form\QualityDimensionType(),
                                  $qualityDimension, 
                                  array(
                                  'action' => $this->generateUrl('qualitydimension-add'),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {  
            $user = $this->getUser();
            $model->insertQualityDimension($qualityDimension, $user);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Dimension added!')
            ; 
            $qualityDimensions = $this->get('session')->get('qualityDimensions',null);
            if ($qualityDimensions)
            {
                $qualityDimensions[] = $qualityDimension;
            }
            else {
                $qualityDimensions = array($qualityDimension);
            }
            $this->get('session')->set('qualityDimensions',$qualityDimensions);
            return $this->redirect($this->generateUrl('qualitydimension-add'));
        }
        
        return $this->render('qualityflow/form.html.twig', array(
            'form' => $form->createView(),
            'qualityDimension' => $qualityDimension
        ));
    }
    
    /**
     * @Route("/qualitydimension/edit/{qualitydimension_uri}", name="qualitydimension-edit")
     */
    public function editAction(Request $request, $qualitydimension_uri)
    {        
        $model = $this->get('model.qualitydimension'); 
        $uri = urldecode($qualitydimension_uri);
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
        ));
    }
    
    /**
     * @Route("/qualitydimension/delete/{qualitydimension_uri}", name="qualitydimension-delete")
     */
    
    public function removeAction(Request $request, $qualitydimension_uri)
    {   
        $model = $this->get('model.qualitydimension');
        $uri = urldecode($qualitydimension_uri);
        
        $qualityDimension = $model->findOneQualityDimension($uri);
        
        if ($qualityDimension)
        {                          
            $model->deleteQualityDimension($qualityDimension);

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality dimension deleted!')
            ;
        }
        //TO-DO verificar
        return $this->redirect($this->generateUrl('qualitydimensions'));
    }
    
    /**
     * @Route("/qualitydimension/reset", name="qualitydimension-reset")
     */
    public function resetAction(Request $request)
    {                   
        $model_provenance = $this->get('model.qualitydimension');         
        $model_provenance->clearGraph();                
                    
        return $this->redirect($this->generateUrl('qualitydimensions'));
    }  
    
}
