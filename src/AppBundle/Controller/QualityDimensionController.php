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
     * @Route("/qualitydimension/add", name="qualitydimension-add")
     */
    public function addAction(Request $request) {
        $em = $this->get('doctrine')->getManager();
        $qualitydimension = new \AppBundle\Entity\QualityDimension();
        $form = $this->createForm(new \AppBundle\Form\QualityDimensionAddType($em),
                                  $qualitydimension, 
                                  array(
                                  'action' => $this->generateUrl('qualitydimension-add'),
                                  'method' => 'POST'
                                  ));
        
        $form->handleRequest($request);
                
        if ($form->isValid()) 
        {  
            $em->persist($qualitydimension);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Dimension added!')
            ; 
            
            $root_path = $this->get('kernel')->getRootDir();

            $model = $this->get('model.qualitydimension'); 
            $model->storeQualityDimension($qualitydimension);
            
            return $this->redirect($this->generateUrl('qualitydimension-edit',
                                    array('qualitydimension_id' => $qualitydimension->getId())));
        }
        
        return $this->render('qualityflow/form.html.twig', array(
            'form' => $form->createView(),
            'qualitydimension' => $qualitydimension
        ));
        
    }
    
    /**
     * @Route("/qualitydimension/delete/{qualitydimension_id}", name="qualitydimension-delete")
     */
    
    public function removeAction(Request $request, $qualitydimension_id)
    {        
        $em = $this->get('doctrine')->getManager();                
        $qualitydimension = $em->getRepository('AppBundle:QualityDimension')
                ->find($qualitydimension_id);
                                
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
        
        return $this->redirect($this->generateUrl('workflows'));
    }
    
}
