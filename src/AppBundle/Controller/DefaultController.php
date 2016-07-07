<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{                
    /**
     * @Route("/reset", name="reset")
     */
    public function resetAction(Request $request)
    {        
        $model_provenance = $this->get('model.provenance');         
        $model_provenance->clearGraph();
        
        $root_path = $this->get('kernel')->getRootDir();

        $model_workflow = $this->get('model.workflow'); 
        $model_workflow->clearDB();
        $model_workflow->clearUploads($root_path);
                    
        return $this->redirect($this->generateUrl('homepage'));
    }     
    
    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {           
        return $this->render('default/index.html.twig', array(
            
        ));
    }     
}


