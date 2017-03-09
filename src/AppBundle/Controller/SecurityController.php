<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{
    public function loginAction()
    {
        $person = new \AppBundle\Entity\Person();
        $form = $this->createForm(new \AppBundle\Form\PersonRegistrationType(), $person, array(
            'action' => $this->generateUrl('registration-form'),
            'method' => 'POST'
        ));
        
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
                'form_registration' => $form->createView()
            )
        );
    } 
    
    /**
     * @Route("/security/registration", name="registration-form")
     */
    public function registrationAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $user = new \AppBundle\Entity\Person();
        $form = $this->createForm(new \AppBundle\Form\PersonRegistrationType(), $user, array(
            'action' => $this->generateUrl('registration-form'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);             
        
        if ($form->isValid()) 
        {                  
            $model = $this->get('model.security');
            
            if ($model->loadUserByUsername($user->getUsername()))
            {
                $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'User already exists on database.')
                ;
            }
            else 
            {
                $model->saveUser($user);
            
                $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Registration complete! Please log in.')
                ;
                
                return $this->redirect($this->generateUrl('login'));
            }                        
        }
        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => null,
                'error' => null,
                'form_registration' => $form->createView()
            )
        );
    }
    
    /**
     * @Route("/security/user-list", name="user-list")
     */
    public function userListAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        
        $model = $this->get('model.security');            
        $users = $model->findAllUsers();
            
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render(
            'security/user-list.html.twig',
            array(
                'pagination' => $pagination
            )
        );
    }
    
    /**
     * @Route("/security/user/{user_uri}", name="security-user")
     */
    public function userAction($user_uri)
    {
        $user_uri = urldecode($user_uri);
        $model = $this->get('model.security');            
        $user = $model->findUserByURI($user_uri);                    
        
        return $this->render(
            'security/user.html.twig',
            array(
                'user' => $user
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