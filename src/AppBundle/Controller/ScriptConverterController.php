<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ScriptConverterController extends Controller
{     
    
    /**
     * @Route("/script-converter/list", name="script-converter-list")
     */
    public function listAction(Request $request)
    {  
        
        return $this->render('script-converter/list.html.twig', array(
            'pagination' => $pagination            
        ));
    }
    /**
     * @Route("/script-converter/form", name="script-converter-form")
     */
    public function uploadAction(Request $request)
    {        
        $converter = new \AppBundle\Entity\ScriptConverter();
        $form = $this->createForm(new \AppBundle\Form\ScriptConverterUploadType(), $converter, array(
            'action' => $this->generateUrl('script-converter-form'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
        
        $script_content = '';
                
        if ($form->isValid()) 
        {  
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw $this->createAccessDeniedException();
            }
            
            $converter->createGraph();
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Abstract Workflow created!')
            ; 

            $script_content = $converter->getScriptCode();         
        }                                                
                    
        return $this->render('script-converter/form.html.twig', array(
            'form' => $form->createView(),
            'script_content' => $script_content,
            'abstract_workflow' => $converter->getUploadDir()."/wf.png"
        ));
    }  
    
    /**
     * @Route("/script-converter/editor", name="script-converter-editor")
     */
    public function editorAction(Request $request)
    {      
        $converter = new \AppBundle\Entity\ScriptConverter();
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
        $root_path = $this->get('kernel')->getRootDir();

        $code = $data['code'];
        $language = $data['language'];
        $hash = $data['hash'];
              
        $user = $this->getUser();
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $converter->setCreator($user);
        $converter->setScriptLanguage($language);                
        $converter->setScriptCode($code, $root_path);                        
        
        $response = new \Symfony\Component\HttpFoundation\Response();        
        
        return $response->setContent($request->getContent());
    }
    
    
    /**
     * @Route("/script-converter/workflow/download", options={"expose"=true}, name="script-converter-workflow-download")
     */
    public function downloadWorkflowAction(Request $request)
    {       
        $root_path = $this->get('kernel')->getRootDir();
        $model = $this->get('model.script-converter');
        $workflow = $model->downloadWorkflow($root_path, "bash");
        
        $content = file_get_contents($workflow);
        $response = new \Symfony\Component\HttpFoundation\Response();   
        
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($workflow));
        $response->headers->set('Content-Disposition', 'attachment; filename="workflow.t2flow";');
        $response->headers->set('Content-length', filesize($workflow));

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response->setContent($content);
    }
    
    /**
     * @Route("/script-converter/workflow/image", options={"expose"=true}, name="script-converter-workflow-image")
     */
    public function imageWorkflowAction(Request $request)
    {       
        $hash = $request->get('hash');
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash($hash);
        $content = $converter->getWorkflowImage();
        
        $array = array('svg' => $content);
        
        $response = new \Symfony\Component\HttpFoundation\Response();                   
        return $response->setContent(json_encode($array));
    }    
}