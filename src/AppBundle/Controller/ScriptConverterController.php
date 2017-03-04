<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;

class ScriptConverterController extends Controller
{                
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
            
            $model = $this->get('model.script-converter'); 
            $model->createGraph($converter);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Abstract Workflow created!')
            ; 
            $fs = new Filesystem();            
            $script_content = file_get_contents($converter->getScriptAbsolutePath());
            //$fs->remove($script-converter->getScriptAbsolutePath());            
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
        return $this->render('script-converter/editor.html.twig', array(
            
        ));
    }
    
    /**
     * @Route("/script-converter/save", name="script-converter-save")
     */
    public function saveAction(Request $request)
    {               
        $data = json_decode($request->getContent(), true);
        
        $code = $data['code'];
        $language = $data['language'];
        
        echo $code;
        
        $user = $this->getUser();
        
        $root_path = $this->get('kernel')->getRootDir();
        
        $fs = new Filesystem();           
        $fs->dumpFile($root_path."/../web/uploads/documents/yesscript/script.sh", $code);
        
        $response = new \Symfony\Component\HttpFoundation\Response();        
        
        return $response->setContent($request->getContent());
    }
    
    
    /**
     * @Route("/script-converter/workflow/download", name="script-converter-workflow-download")
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
     * @Route("/script-converter/workflow/image", name="script-converter-workflow-image")
     */
    public function imageWorkflowAction(Request $request)
    {       
        $root_path = $this->get('kernel')->getRootDir();
        $image = $root_path."/../web/uploads/documents/yesscript/workflow.svg";
        
        $content = file_get_contents($image);
        
        $array = array('svg' => $content);
        
        $response = new \Symfony\Component\HttpFoundation\Response();                   
        return $response->setContent(json_encode($array));
    }    
}