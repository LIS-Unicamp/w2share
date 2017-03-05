<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ScriptConverterController extends Controller
{     
    
    /**
     * @Route("/script-converter", name="script-converter")
     */
    public function indexAction(Request $request)
    {                          
        return $this->render('script-converter/index.html.twig', array(
        ));
    }
    
    /**
     * @Route("/script-converter/list", name="script-converter-list")
     */
    public function listAction(Request $request)
    {  
        $model = $this->get('model.scriptconverter');
        $conversions = $model->findScriptConversions();
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $conversions, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('script-converter/list.html.twig', array(
            'pagination' => $pagination            
        ));
    }
    
    /**
     * @Route("/script-converter/upload", name="script-converter-upload")
     */
    public function uploadAction(Request $request)
    {        
        $hash = $request->get('hash');
        $workflow = new \AppBundle\Entity\Workflow();
        $form = $this->createForm(new \AppBundle\Form\ScriptConverterUploadType(), $workflow, array(
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                        
        if ($form->isValid()) 
        {       
            $workflow->setHash($hash);
            $workflow->preUpload();
            $workflow->upload();
            
            $model = $this->get('model.workflow');             
            $model->addWorkflow($workflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow added!')
            ; 
        }                                                
                    
        return $this->render('script-converter/upload.html.twig', array(
            'form' => $form->createView(),
        ));
    }  
    
    /**
     * @Route("/script-converter/editor", name="script-converter-editor")
     */
    public function editorAction(Request $request)
    {      
        $converter = new \AppBundle\Entity\ScriptConverter();
        if ($request->get("hash"))
        {
            $hash = $request->get('hash');
            $this->get('session')->set('hash', $hash);  
            $converter->setHash($hash);
        }
        else if ($this->get('session')->get('hash'))
        {
            $hash = $this->get('session')->get('hash'); 
            $converter->setHash($hash);
        }
        else
        {            
            $this->get('session')->set('hash', $converter->getHash());    
        }
        
        $language = $this->get('session')->get('language');
        $converter->setScriptLanguage($language);
        
        return $this->render('script-converter/editor.html.twig', array(
            'converter' => $converter
        ));
    }
    
    /**
     * @Route("/script-converter/save", options={"expose"=true}, name="script-converter-save")
     */
    public function saveAction(Request $request)
    {               
        $data = json_decode($request->getContent(), true);

        $code = $data['code'];
        $language = $data['language'];
        $hash = $this->get('session')->get('hash');
        $this->get('session')->set('language', $language);
        
        $user = $this->getUser();
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $converter->setCreator($user);
        $converter->setScriptLanguage($language);                
        $converter->setScriptCode($code);  
        $converter->createWorkflow();
        
        $model = $this->get('model.scriptconverter');
        $model->insertScriptConversion($converter, $user);
        
        $response = new \Symfony\Component\HttpFoundation\Response();        
        
        return $response->setContent('ok');
    }
    
    /**
     * @Route("/script-converter/restart", name="script-converter-restart")
     */
    public function restartAction(Request $request)
    {               
        $this->get('session')->set('hash', null);    
        $this->get('session')->set('language', null); 
        return $this->redirect($this->generateUrl('script-converter-editor'));
    }
    
    /**
     * @Route("/script-converter/delete", name="script-converter-delete")
     */
    public function deleteAction(Request $request)
    {               
        $hash = $request->get('hash');    


        return $this->redirect($this->generateUrl('script-converter-editor'));
    }
    
    
    /**
     * @Route("/script-converter/workflow/download", options={"expose"=true}, name="script-converter-workflow-download")
     */
    public function downloadWorkflowAction(Request $request)
    {       
        $language = $this->get('session')->get('language');        
        $hash = $this->get('session')->get('hash');
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setScriptLanguage($language);
        $converter->setHash($hash);
        $content = $converter->getWorkflowT2FlowFile();
        $file_path = $converter->getWorkflowT2FlowFilepath();
                
        $response = new \Symfony\Component\HttpFoundation\Response();   
        
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($file_path));
        $response->headers->set('Content-Disposition', 'attachment; filename="workflow.t2flow";');
        $response->headers->set('Content-length', filesize($file_path));

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response->setContent($content);
    }
    
    /**
     * @Route("/script-converter/workflow/image", options={"expose"=true}, name="script-converter-workflow-image")
     */
    public function imageWorkflowAction(Request $request)
    {       
        $hash = $this->get('session')->get('hash');        
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $content = $converter->getWorkflowImage();
        
        $array = array('svg' => $content);
        
        $response = new \Symfony\Component\HttpFoundation\Response();                   
        return $response->setContent(json_encode($array));
    }    
}