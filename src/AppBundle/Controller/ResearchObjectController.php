<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResearchObjectController extends Controller
{        
    /**
     * @Route("/ro-list", name="ro-list")
     */
    public function listAction(Request $request)
    {         
        $model = $this->get('model.ro');         
        $ros = $model->findAll();
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $ros, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('ro/list.html.twig', array(
            'pagination' => $pagination
        ));
    }          
    
    /**
     * @Route("/ro/add", name="ro-add")
     */
    public function addResearchObjectAction(Request $request)
    {                
        $ro = new \AppBundle\Entity\ResearchObject();
        
        $form = $this->createForm(new \AppBundle\Form\ResearchObjectType(), $ro, array(
            'action' => $this->generateUrl('ro-add'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {       
            $ro->preUpload();
            $ro->upload();
            
            $model = $this->get('model.ro');             
            $model->addResearchObject($ro);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'ResearchObject added!')
            ; 
            return $this->redirect($this->generateUrl('ro-list'));
        }
        
        return $this->render('ro/form.html.twig', array(
            'form' => $form->createView(),
            'ro' => $ro
        ));
    }
    
    /**
     * @Route("/ro/delete/{ro_uri}", name="ro-delete")
     */
    public function removeResearchObjectAction(Request $request, $ro_uri)
    {                
        $ro_uri = urldecode($ro_uri);
                
        $model = $this->get('model.ro'); 
        $ro = $model->findResearchObject($ro_uri);

        if ($ro)
        {
            $model->deleteResearchObject($ro);                        

            $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'ResearchObject deleted!')
                ;
        }
        else
        {
            $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'ResearchObject does not exist!')
                ;
        }
        
        return $this->redirect($this->generateUrl('ro-list'));
    }
    
    /**
     * @Route("/ro/edit/{ro_uri}", name="ro-edit")
     */
    public function editResearchObjectAction(Request $request, $ro_uri)
    {             
        $ro_uri = urldecode($ro_uri);
        
        $model = $this->get('model.ro');                                   
        $ro = $model->findResearchObject($ro_uri);
        
        if (null === $ro)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('ResearchObject not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\ResearchObjectType(), $ro);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                     
            $ro->preUpload();            
            $ro->upload();
            
            $model = $this->get('model.ro'); 
            $model->editResearchObject($ro);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'ResearchObject edited!')
            ; 
        }
        
        return $this->render('ro/form.html.twig', array(
            'form' => $form->createView(),
            'ro' => $ro
        ));
    }       
    
    /**
     * @Route("/ro/process/{process_uri}", name="ro-process")
     */
    public function roProcessAction(Request $request)
    {
        $process_uri = urldecode($request->get('process_uri'));

        $model = $this->get('model.ro');             
        $process_inputs = $model->findProcessInputs($process_uri);
        $process_outputs = $model->findProcessOutputs($process_uri);
        $process = $model->findProcess($process_uri);
        $ro = $model->findResearchObject($process->getResearchObject()->getUri());
        
        $model_provenance = $this->get('model.provenance');             
        $processRuns = $model_provenance->findProcessRunsByProcess($process_uri);
                
        return $this->render('ro/process.html.twig', array(
            'process' => $process,
            'process_uri' => $process_uri,
            'process_inputs' => $process_inputs,
            'process_outputs' => $process_outputs,
            'ro' => $ro,
            'processRuns' => $processRuns
        ));
    }
    
    /**
     * @Route("/ro/details/{ro_uri}", name="ro-details")
     */
    public function roAction(Request $request, $ro_uri)
    {       
        $ro_uri = urldecode($ro_uri);
        
        $model = $this->get('model.ro');                                   
        $ro = $model->findResearchObject($ro_uri);
                
        return $this->render('ro/ro.html.twig', array(

            'ro' => $ro,
            'ro_uri' => $ro_uri
        ));
    }
    
    /**
     * @Route("/ro/reset", name="ro-reset")
     */
    public function resetAction(Request $request)
    {                                   
        $model_ro = $this->get('model.ro'); 
        $model_ro->clearGraph();
        $model_ro->clearUploads();
                    
        return $this->redirect($this->generateUrl('ro-list'));
    }   
}

