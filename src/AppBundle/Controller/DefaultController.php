<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{                      
    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction()
    {           
        return $this->render('default/index.html.twig', array(
            
        ));
    }   
    
    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {           
        return $this->render('default/about.html.twig', array(
            
        ));
    }  
    
    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction()
    {           
        return $this->render('default/contact.html.twig', array(
            
        ));
    }  
}


