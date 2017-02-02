<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of QualityDimensionController
 *
 * @author joana
 */
class QualityDimensionController extends Controller{
    
    /**
     * @Route("/qualitydimension", name="qualitydimensions")
     */
    public function indexAction(Request $request)
    {         
        $result = $this->get('doctrine')
            ->getRepository('AppBundle:QualityDimension')->findAll();
        
        /*return $this->render('qualitydimension/index.html.twig', array(
            'result' => $result
        )); */
       
        return array(
            'result' => $result
        );
    }

    /**
     * @Route("/qualitydimension/add", name="qualitydimension-add")
     */
    public function addAction(Request $request) {
        
        $model = $this->get('model.qualitydimension'); 
        
        $qualityDimension = new \AppBundle\Entity\QualityDimension();
        $form = $this->createForm(new \AppBundle\Form\QualityDimensionAddType(),
                                  $qualityDimension, 
                                  array(
                                  'action' => $this->generateUrl('qualitydimension-add'),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {  
            $model->insertQualityDimension($qualityDimension);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Dimension added!')
            ; 
            $qualityDimensions = $this->get('session')->get('qualityDimensions',null);
            if ($qualityDimensions)
            {
                $qualityDimensions[] = $qualityDimension;
            }
            else {
                $qualityDimensions = array($qualityDimension);
            }
            $this->get('session')->set('qualityDimensions',$qualityDimensions);
            return $this->redirect($this->generateUrl('qualitydimension-add'));
        }
        
        return $this->render('qualityflow/form.html.twig', array(
            'form' => $form->createView(),
            'qualityDimension' => $qualityDimension
        ));
    }
    
    /**
     * @Route("/qualitydimension/edit/{qualitydimension_uri}", name="qualitydimension-edit")
     */
    public function editAction(Request $request, $qualitydimension_uri)
    {        
        $model = $this->get('model.qualitydimension'); 
        $uri = urldecode($qualitydimension_uri);
        $qualityDimension = $model->findOneQualityDimension($uri);
        
        $form = $this->createForm(new \AppBundle\Form\QualityDimensionAddType(), $qualityDimension);
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {              
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality dimension edited!')
            ;            
        }
        
        return $this->render('qualityflow/form.html.twig', array(
            'form' => $form->createView(),
            'qualityDimension' => $qualityDimension
        ));
    }
    
    /**
     * @Route("/qualitydimension/delete/{qualitydimension_uri}", name="qualitydimension-delete")
     */
    
    public function removeAction(Request $request, $qualitydimension_uri)
    {        
        $em = $this->get('doctrine')->getManager();                
        $qualitydimension = $em->getRepository('AppBundle:QualityDimension')
                ->find($qualitydimension_uri);
                                
        if ($qualitydimension)  
        {                          
            $model = $this->get('model.qualitydimension'); 
            $model->deleteQualityDimension($qualitydimension->getId());
                        
            $em->remove($qualitydimension);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality dimension deleted!')
            ;
        }
        //TO-DO verificar
        return $this->redirect($this->generateUrl('qualitydimension'));
    }
    
}
