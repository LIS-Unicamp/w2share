<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );
    } 
    
    /**
     * @Route("/security/registration", name="registration-form")
     */
    public function registrationAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $person = new \AppBundle\Entity\Person();
        $form = $this->createForm(new \AppBundle\Form\PersonRegistrationType(), $person, array(
            'action' => $this->generateUrl('registration-form'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);             
        
        if ($form->isValid()) 
        {                                                
            $query = $model->findQualityDimensionsByUser($user);
        }
        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error' => false,
                'form_registration' => $form->createView()
            )
        );
    }
    
    /**
     * @Route("/security/reset", name="security-reset")
     */
    public function resetAction()
    {                   
        $model = $this->get('model.security');         
        $model->clearGraph();                
                    
        return $this->redirect($this->generateUrl('logout'));
    }   
}