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
        $dao = $this->get('dao.wro');         
        $wros = $dao->findAll();
        
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
     * @Route("/wro/refresh/{wro_uri}", name="wro-refresh")
     */
    public function refreshWROAction(Request $request, $wro_uri)
    {  
        
    }
    
    /**
     * Create a new WRO using a form.
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
        $dao = $this->get('dao.wro'); 
        $wro = $dao->findWRO($wro_uri);

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
     * @Route("/wro/resource/delete/{resource_uri}", name="wro-resource-delete")
     */
    public function removeResourceAction($resource_uri)
    {                
        $resource_uri = urldecode($resource_uri);
                
        $dao = $this->get('dao.wro');
        $resource = $dao->findResource($resource_uri);        
        
        if ($resource)
        {
            $dao->deleteWROResource($resource_uri);
            $resource->removeUpload();
            
            $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Resource deleted!')
                ;
        }
        else 
        {
            $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'Resource not found!')
                ;
        }
        
        return $this->redirect($this->generateUrl('wro-details', array('wro_uri'=>  urlencode($resource->getWro()->getUri()))));
    }        
    
    /**
     * @Route("/wro/resource/add/{wro_uri}", name="wro-resource-add")
     */
    public function addResourceAction(Request $request, $wro_uri)
    {             
        $wro_uri = urldecode($wro_uri);
        
        $dao = $this->get('dao.wro');  
        $wro = $dao->findWRO($wro_uri);
        
        $resource = new \AppBundle\Entity\WROResource();
        $resource->setWro($wro);
        
        $form = $this->createForm(new \AppBundle\Form\WROResourceType(), $resource);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                                 
            $dao->addResource($resource);
            $resource->upload();
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Resource created!')
            ; 
        }
        
        return $this->render('wro/resource-form.html.twig', array(
            'form' => $form->createView(),
            'resource' => $resource
        ));
    }
    
    /**
     * @Route("/wro/resource/edit/{resource_uri}", name="wro-resource-edit")
     */
    public function editResourceAction(Request $request, $resource_uri)
    {             
        $resource_uri = urldecode($resource_uri);
        
        $dao = $this->get('dao.wro');                                   
        $resource = $dao->findResource($resource_uri);
        
        if (null === $resource)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Resource not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\WROResourceType(), $resource);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                                 
            $dao->updateResource($resource);
            $resource->upload();
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Resource edited!')
            ; 
        }
        
        return $this->render('wro/resource-form.html.twig', array(
            'form' => $form->createView(),
            'resource' => $resource
        ));
    }

    /**
     * @Route("/wro/qed/add/{wro_uri}", name="wro-qed-add")
     */
    public function addQEDAction(Request $request, $wro_uri)
    {
        $wro_uri = urldecode($wro_uri);

        $dao = $this->get('dao.wro');
        $wro = $dao->findWRO($wro_uri);
        $model = $this->get('model.qualitydatatype');
        $qed = new \AppBundle\Entity\QualityEvidenceData();

        $form = $this->createForm(new \AppBundle\Form\QualityEvidenceDataType($dao, $model, $wro), $qed);
        $qed->setWro($wro);

        $form->handleRequest($request);

        if ($form->isValid())
        {

            $now = new \Datetime();
            $qed->setCreator($this->getUser());
            $qed->setCreatedAtTime($now);
            $dao->addQED($qed);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Evidence Data added!')
            ;

        }

        return $this->render('wro/qed-form.html.twig', array(
            'form' => $form->createView(),
            'qed' => $qed
        ));
    }


    /**
     * @Route("/wro/qed/delete/{qed_uri}", name="wro-qed-delete")
     */
    public function removeQEDAction($qed_uri)
    {
        $qed_uri = urldecode($qed_uri);

        $dao = $this->get('dao.wro');
        $qed = $dao->findQED($qed_uri);
        echo $qed->getResource()->getFilename();

        if ($qed)
        {
            $dao->deleteQED($qed_uri);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Resource deleted!')
            ;
        }
        else
        {
            $this->get('session')
                ->getFlashBag()
                ->add('error', 'Resource not found!')
            ;
        }

        return $this->redirect($this->generateUrl('wro-details', array('wro_uri'=>  urlencode($qed->getWro()->getUri()))));
    }

    /**
     * @Route("/wro/edit/{wro_uri}", name="wro-edit")
     */
    public function editWROAction(Request $request, $wro_uri)
    {             
        $wro_uri = urldecode($wro_uri);
        
        $model = $this->get('model.wro');                                   
        $dao = $this->get('dao.wro');                                   
        $wro = $dao->findWRO($wro_uri);
        
        if (null === $wro)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('WRO not found!');
        }
        
        $form = $this->createForm(new \AppBundle\Form\WROType(), $wro);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {                                     
            $wro->upload();
            
            $dao->updateWRO($wro);
            
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
        
        $dao = $this->get('dao.wro');                                   
        $wro = $dao->findWRO($wro_uri);
        $file_path = $wro->getWROAbsolutePath();
        $content = $wro->getWROFileContent();
                
        $response = new \Symfony\Component\HttpFoundation\Response();   
        
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($file_path));
        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($file_path).'";');
        $response->headers->set('Content-length', filesize($file_path));

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response->setContent($content);
    }    
    
    /**
     * @Route("/wro/details/{wro_uri}", name="wro-details")
     */
    public function wroAction(Request $request, $wro_uri)
    {       
        $wro_uri = urldecode($wro_uri);
        
        $dao = $this->get('dao.wro');
        $qdtmodel = $this->get('model.qualitydatatype');
        $wro = $dao->findWRO($wro_uri);
        $percent = (sizeof($wro->getQualityEvidenceData()) /sizeof($qdtmodel->findAllQualityDataTypes()))*100;
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $wro->getResources(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
                
        return $this->render('wro/wro.html.twig', array(
            'wro' => $wro,
            'pagination'=>$pagination,
            'qeds'=> $wro->getQualityEvidenceData(),
            'percent'=> $percent
        ));
    }

    
    /**
     * @Route("/wro/resource/download/{resource_uri}", name="wro-resource-download")
     */
    public function downloadWROResourceAction($resource_uri)
    {     
        $dao = $this->get('dao.wro');
        $resource = $dao->findResource($resource_uri);
        
        $file_path = $resource->getAbsolutePath();
        $content = $resource->getFileContent();
                
        $response = new \Symfony\Component\HttpFoundation\Response();   
        
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($file_path));
        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($file_path).'";');
        $response->headers->set('Content-length', filesize($file_path));

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response->setContent($content);
    }
    
    /**
     * @Route("/wro/resource/details/{resource_uri}", name="wro-resource-details")
     */
    public function WROResourceDetailsAction($resource_uri)
    {     
        $resource_uri = urldecode($resource_uri);
        $dao = $this->get('dao.wro');
        $resource_uri = urldecode($resource_uri);
        $resource = $dao->findResource($resource_uri);                  
        
        if (null === $resource)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Resource not found!');
        }
        
        $wro = $dao->findWro($resource->getWro()->getUri());
        $resource->setWro($wro);
        
        return $this->render('wro/resource.html.twig', array(
            'resource' => $resource
        ));
    }
    
    /**
     * @Route("/wro/reset", name="wro-reset")
     */
    public function resetAction(Request $request)
    {                                   
        $model = $this->get('model.wro');         
        $model->resetData();
                    
        return $this->redirect($this->generateUrl('wro-list'));
    }   
}

