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
class QualityDimensionController extends Controller
{    
    /**
     * @Route("/quality-dimension/list", name="quality-dimension-list")
     */
    public function indexAction(Request $request)
    {         
        $model = $this->get('model.qualitydimension');
        $users = $model->findUsersWithQualityDimensions();

        $form = $this->createForm(new \AppBundle\Form\QualityDimensionFilterType($users), null, array(
            'action' => $this->generateUrl('quality-dimension-list'),
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
        
        return $this->render('quality-dimension/list.html.twig', array(
            'pagination' => $pagination,
            'form' => $form->createView()
        ));       
    }

    /**
     * @Route("/quality-dimension/add", name="quality-dimension-add")
     */
    public function addAction(Request $request) {
        
        $model = $this->get('model.qualitydimension'); 
        
        $qualityDimension = new \AppBundle\Entity\QualityDimension();
        $form = $this->createForm(new \AppBundle\Form\QualityDimensionType(),
                                  $qualityDimension, 
                                  array(
                                  'action' => $this->generateUrl('quality-dimension-add'),
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
            
            return $this->redirect($this->generateUrl('quality-dimension-add'));
        }
        
        return $this->render('quality-dimension/form.html.twig', array(
            'form' => $form->createView(),
            'qualityDimension' => $qualityDimension
        ));
    }
    
    /**
     * @Route("/quality-dimension/edit/{qualitydimension_uri}", name="quality-dimension-edit")
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
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality dimension edited!')
            ;
        }
        
        return $this->render('quality-dimension/form.html.twig', array(
            'form' => $form->createView(),
            'qualityDimension' => $qualityDimension
        ));
    }
    
    /**
     * @Route("/quality-dimension/delete/{qualitydimension_uri}", name="quality-dimension-delete")
     */
    
    public function removeAction(Request $request, $qualitydimension_uri)
    {   
        $model = $this->get('model.qualitydimension');
        $uri = urldecode($qualitydimension_uri);
        
        $qualityDimension = $model->findOneQualityDimension($uri);
        
        if ($qualityDimension)
        {
            if($model->qualityDimensionBeingUsed($qualityDimension)){
                $this->get('session')
                    ->getFlashBag()
                    ->add(
                    'notice', 'This quality dimension is being used by a quality data type and it cannot be deleted' );
            }
            else {
                $model->deleteQualityDimension($qualityDimension);

                $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Quality dimension deleted!');
            }
        }
        //TO-DO verificar
        return $this->redirect($this->generateUrl('quality-dimension-list'));
    }
    
    /**
     * @Route("/quality-dimension/reset", name="quality-dimension-reset")
     */
    public function resetAction(Request $request)
    {                   
        $model_provenance = $this->get('model.qualitydimension');         
        $model_provenance->clearGraph();                
                    
        return $this->redirect($this->generateUrl('quality-dimension-list'));
    }            
}