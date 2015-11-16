<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnnotationController extends Controller
{                
    /**
     * @Route("/annotate", name="annotate-form")
     */
    public function annotateAction(Request $request)
    {        
        $uri = $request->get('uri');
        $artefact = $request->get('artefact');
        
        $model = $this->get('model.annotation'); 
        $model->ontology();

                    
        return $this->render('annotation/form.html.twig', array(
            
        ));
    }        
}


