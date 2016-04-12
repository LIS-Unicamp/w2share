<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

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
}


