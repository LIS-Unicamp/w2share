<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;

class YesWorkflowController extends Controller
{                
    /**
     * @Route("/yesworkflow/form", name="yesworkflow-form")
     */
    public function uploadAction(Request $request)
    {        
        $yesworkflow = new \AppBundle\Entity\YesWorkflow();
        $form = $this->createForm(new \AppBundle\Form\YesWorkflowUploadType(), $yesworkflow, array(
            'action' => $this->generateUrl('yesworkflow-form'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
        
        $script_content = '';
                
        if ($form->isValid()) 
        {  
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw $this->createAccessDeniedException();
            }
            
            $model = $this->get('model.yesworkflow'); 
            $model->createGraph($yesworkflow);
            
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Abstract Workflow created!')
            ; 
            $fs = new Filesystem();            
            $script_content = file_get_contents($yesworkflow->getScriptAbsolutePath());
            //$fs->remove($yesworkflow->getScriptAbsolutePath());            
        }                                                
                    
        return $this->render('yesworkflow/form.html.twig', array(
            'form' => $form->createView(),
            'script_content' => $script_content,
            'abstract_workflow' => $yesworkflow->getUploadDir()."/wf.png"
        ));
    }  
    
    /**
     * @Route("/yesworkflow/editor", name="yesworkflow-editor")
     */
    public function editorAction(Request $request)
    {                                                                                   
        return $this->render('yesworkflow/editor.html.twig', array(
            
        ));
    }
    
    /**
     * @Route("/yesworkflow/save", name="yesworkflow-save")
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
     * @Route("/yesworkflow/workflow/download", name="yesworkflow-workflow-download")
     */
    public function downloadWorkflowAction(Request $request)
    {       
        $root_path = $this->get('kernel')->getRootDir();
        $model = $this->get('model.yesworkflow');
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
     * @Route("/yesworkflow/workflow/image", name="yesworkflow-workflow-image")
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