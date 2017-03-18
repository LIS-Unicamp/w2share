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
     * @Route("/yw-graph-service/api/v1/graph", options={"expose"=true}, name="yw-graph-service")
     */
    public function graphServiceAPIAction(Request $request)
    {               
        $data = json_decode($request->getContent(), true);
        
        $model = $this->get('model.scriptconverter');
        $content = $model->createGraphServiceResponse($data);
        
        $response = new \Symfony\Component\HttpFoundation\Response();                   
        return $response->setContent($content);
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
        if ($request->get("hash"))
        {
            $hash = $request->get('hash');
        }
        else if ($this->get('session')->get('hash'))
        {
            $hash = $this->get('session')->get('hash'); 
        }
        
        $model = $this->get('model.scriptconverter');
        $conversion = $model->findOneScriptConversionByHash($hash);
        
        $workflow = new \AppBundle\Entity\Workflow();
        $form = $this->createForm(new \AppBundle\Form\ScriptConverterUploadType(), $workflow, array(
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
                        
        if ($form->isValid()) 
        {       
            $workflow->setHash($hash);
            $workflow->upload();            
            
            $model = $this->get('model.scriptconverter');             
            $model->addWorkflow($workflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow added!')
            ; 
        }                                                
                    
        return $this->render('script-converter/upload.html.twig', array(
            'form' => $form->createView(),
            'conversion' => $conversion
        ));
    }  
    
    /**
     * @Route("/script-converter/details/{hash}", name="script-converter-details")
     */
    public function detailsAction(Request $request)
    {                       
        $hash = $request->get('hash');                        
        $model = $this->get('model.scriptconverter');
        
        $conversion = $model->findOneScriptConversionByHash($hash);        
        
        return $this->render('script-converter/details.html.twig', array(
            'conversion' => $conversion,
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
        
        if ($request->get('language'))
        {
            $language = $request->get('language');
            $this->get('session')->set('language', $language);
        }
        else {
            $language = $this->get('session')->get('language');
        }
                
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
        
        $previous = $model->findOneScriptConversionByHash($hash);
        if ($previous)
        {
            $converter->setUri($previous->getUri());
            $converter->setCreatedAt($previous->getCreatedAt());
            $model->updateScriptConversion($converter);
        }
        else {
            $model->insertScriptConversion($converter, $user);
        }
        
        
        $response = new \Symfony\Component\HttpFoundation\Response();        
        
        return $response->setContent('ok');
    }
    
    /**
     * @Route("/yesworkflow/editor", name="yesworkflow")
     */
    public function yesworkflowAction(Request $request)
    {               
        return $this->redirect($this->generateUrl('script-converter-editor'));
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
     * @Route("/script-converter/create-wro", name="script-converter-create-wro")
     */
    public function createWROAction(Request $request)
    {   
        $model = $this->get('model.wro');
        $conversion = $this->get('model.scriptconverter')->findOneScriptConversionByHash($request->get('hash'));
        $model->createWRO($conversion);
        $this->get('session')
                ->getFlashBag()
                ->add('success', 'Workflow Research Object created!')
            ; 
        
        return $this->redirect($this->generateUrl('script-converter-list'));
    }
    
    /**
     * @Route("/script-converter/delete", name="script-converter-delete")
     */
    public function deleteAction(Request $request)
    {               
        $hash = $request->get('hash');
        
        $model = $this->get('model.scriptconverter');
        $conversion = $model->findOneScriptConversionByHash($hash);
        
        if (null == $conversion)
        {
            $this->get('session')
                ->getFlashBag()
                ->add('error', 'Conversion does not exist!')
            ; 
        }
        else
        {
            $model->deleteScriptConversion($conversion);
            
            $this->get('session')
                ->getFlashBag()
                ->add('error', 'Conversion deleted!')
            ; 
        }
                       
        return $this->redirect($this->generateUrl('script-converter-list'));
    }
    
    /**
     * @Route("/script-converter/abstract-workflow/download/{hash}/{language}", name="script-converter-abstract-workflow-download")
     */
    public function downloadAbstractWorkflowAction($hash, $language)
    {     
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setScriptLanguage($language);
        $converter->setHash($hash);
        
        $file_path = $converter->getAbstractWorkflowFilepath();
        $content = $converter->getAbstractWorkflowFile();
                
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
     * @Route("/script-converter/provenance/download/{hash}", name="script-converter-provenance-download")
     */
    public function downloadProvenanceDataAction($hash)
    {     
        $workflow = new \AppBundle\Entity\Workflow();
        $workflow->setHash($hash);
        
        $file_path = $workflow->getProvenanceAbsolutePath();
        $content = $workflow->getProvenanceDataFile();
                
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
     * @Route("/script-converter/script/download/{hash}/{language}", name="script-converter-script-download")
     */
    public function downloadScriptAction($hash, $language)
    {                 
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setScriptLanguage($language);
        $converter->setHash($hash);
        
        $file_path = $converter->getScriptFilepath();
        $content = $converter->getScriptCode();
                
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
     * @Route("/script-converter/workflow/image/download", options={"expose"=true}, name="script-converter-workflow-image-download")
     */
    public function workflowImageDownloadAction(Request $request)
    {       
        $hash = $this->get('session')->get('hash');        
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $content = $converter->getWorkflowImage();
        $file_path = $converter->getWorkflowImageFilePath();
        
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
     * @Route("/script-converter/draft-workflow/download", options={"expose"=true}, name="script-converter-draft-workflow-download")
     */
    public function downloadDraftWorkflowAction(Request $request)
    {      
        if ($request->get('hash') && $request->get('language'))
        {
            $language = $request->get('language');        
            $hash = $request->get('hash');
        }
        else
        {
            $language = $this->get('session')->get('language');        
            $hash = $this->get('session')->get('hash');
        }
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setScriptLanguage($language);
        $converter->setHash($hash);
        $content = $converter->getDraftWorkflowT2FlowFile();
        $file_path = $converter->getDraftWorkflowT2FlowFilepath();
                
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
     * @Route("/script-converter/draft-workflow/image/download", options={"expose"=true}, name="script-converter-draft-workflow-image-download")
     */
    public function draftWorkflowImageDownloadAction(Request $request)
    {       
        $hash = $this->get('session')->get('hash');        
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $content = $converter->getDraftWorkflowImage();
        $file_path = $converter->getDraftWorkflowImageFilePath();
        
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
     * @Route("/script-converter/draft-workflow/image", options={"expose"=true}, name="script-converter-draft-workflow-image")
     */
    public function workflowImageJsonAction(Request $request)
    {       
        $hash = $this->get('session')->get('hash');        
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $content = $converter->getDraftWorkflowImage();
        
        $array = array('svg' => $content);
        
        $response = new \Symfony\Component\HttpFoundation\Response();                   
        return $response->setContent(json_encode($array));
    } 
    
    /**
     * @Route("/script-converter/reset", name="script-converter-reset")
     */
    public function resetAction()
    {         
        $model = $this->get('model.scriptconverter');
        $model->clearGraph();
        $model->clearUploads();
        return $this->redirect($this->generateUrl('script-converter-list'));
    }
}