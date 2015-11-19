<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnnotationController extends Controller
{                
    /**
     * @Route("/annotation/form", name="annotatation-form")
     */
    public function annotateAction(Request $request)
    {        
        $uri = urldecode($request->get('uri'));
        $artefact = $request->get('artefact');
        
        $model = $this->get('model.annotation'); 
        $ontology = $model->ontology();
        
        $object = $request->get('object');
        $subject = $request->get('subject');
        $property = $request->get('property');
        
        if ($object)
        {
            $model->insertAnnotation($subject, $property, $object);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Annotation created!')
            ; 
        }
                    
        return $this->render('annotation/form.html.twig', array(
            'object' => $object,
            'ontology' => $ontology,
            'subject' => $subject,
            'property' => $property
        ));
    }    
    
    /**
     * @Route("/annotatation/list", name="annotatation-list")
     */
    public function annotatationListAction(Request $request)
    {        
        $uri = urldecode($request->get('uri'));
        $artefact = $request->get('artefact');
        $object = $request->get('object');

        $model = $this->get('model.annotation'); 
        $annotations = $model->listAnnotations($uri);
        
        $model_provenance = $this->get('model.provenance'); 
        if ($artefact == 'process')
        {
            $object = $model_provenance->process($uri);
        }
                    
        return $this->render('annotation/list.html.twig', array(
            'object' => $object,
            'uri' => $uri,
            'annotations' => $annotations
        ));
    }  
    
    /**
     * @Route("/annotatation/reset", name="annotation-reset")
     */
    public function annotatationResetAction(Request $request)
    {                
        $model = $this->get('model.annotation'); 
        $model->clearGraph();
        
        return $this->redirect($this->generateUrl('homepage'));
    }  
}


