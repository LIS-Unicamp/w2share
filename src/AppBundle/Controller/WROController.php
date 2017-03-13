<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WROController extends Controller
{        
    /**
     * @Route("/wro-list", name="wro-list")
     */
    public function listAction(Request $request)
    {         
        $model = $this->get('model.wro');         
        $wros = $model->findAll();
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $wros, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('wro/list.html.twig', array(
            'pagination' => $pagination
        ));
    }          
    
    /**
     * @Route("/wro/add", name="wro-add")
     */
    public function addWROAction(Request $request)
    {                
        $wro = new \AppBundle\Entity\WRO();
        
        $form = $this->createForm(new \AppBundle\Form\WROType(), $wro, array(
            'action' => $this->generateUrl('wro-add'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {       
            $wro->preUpload();
            $wro->upload();
            
            $model = $this->get('model.wro');             
            $model->addWRO($wro);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'WRO added!')
            ; 
            return $this->redirect($this->generateUrl('wro-list'));
        }
        
        return $this->render('wro/form.html.twig', array(
            'form' => $form->createView(),
            'wro' => $wro
        ));
    }
    
    /**
     * @Route("/wro/delete/{wro_uri}", name="wro-delete")
     */
    public function removeWROAction(Request $request, $wro_uri)
    {                
        $wro_uri = urldecode($wro_uri);
                
        $model = $this->get('model.wro'); 
        //$wro = $model->findWRO($wro_uri);
        $wro = new \AppBundle\Entity\WRO();
        $wro->setUri($wro_uri);

        if ($wro)
        {
            $model->deleteWRO($wro);                        

            $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'WRO deleted!')
                ;
        }
        else
        {
            $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'WRO does not exist!')
                ;
        }
        
        return $this->redirect($this->generateUrl('wro-list'));
    }
    
    /**
     * @Route("/wro/edit/{wro_uri}", name="wro-edit")
     */
    public function editWROAction(Request $request, $wro_uri)
    {             
        $wro_uri = urldecode($wro_uri);
        
        $model = $this->get('model.wro');                                   
        $wro = $model->findWRO($wro_uri);
        
        if (null === $wro)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('WRO not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\WROType(), $wro);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                     
            $wro->preUpload();            
            $wro->upload();
            
            $model = $this->get('model.wro'); 
            $model->editWRO($wro);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'WRO edited!')
            ; 
        }
        
        return $this->render('wro/form.html.twig', array(
            'form' => $form->createView(),
            'wro' => $wro
        ));
    } 
    
    /**
     * @Route("/wro/download/{wro_uri}", name="wro-download")
     */
    public function downloadAction(Request $request, $wro_uri)
    {       
        $wro_uri = urldecode($wro_uri);
        
        $model = $this->get('model.wro');                                   
        $wro = $model->findWRO($wro_uri);
                
        return $this->render('wro/wro.html.twig', array(

            'wro' => $wro,
            'wro_uri' => $wro_uri
        ));
    }    
    
    /**
     * @Route("/wro/details/{wro_uri}", name="wro-details")
     */
    public function wroAction(Request $request, $wro_uri)
    {       
        $wro_uri = urldecode($wro_uri);
        
        $model = $this->get('model.wro');                                   
        $wro = $model->findWRO($wro_uri);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $wro->getResources(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
                
        return $this->render('wro/wro.html.twig', array(
            'wro' => $wro,
            'pagination'=>$pagination
        ));
    }
    
    /**
     * @Route("/wro/reset", name="wro-reset")
     */
    public function resetAction(Request $request)
    {                                   
        $model_wro = $this->get('model.wro'); 
        $model_wro->clearGraph();
        $model_wro->clearUploads();
                    
        return $this->redirect($this->generateUrl('wro-list'));
    }   
}

