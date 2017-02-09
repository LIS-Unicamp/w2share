<?php
namespace AppBundle\Security;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuthenticationHandler
 *
 * @author lucas
 */

class AuthenticationHandler extends ContainerAware implements AuthenticationSuccessHandlerInterface
{
    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {          
        $referer_url = $request->get('referer', $request->headers->get('referer')); 
        
        $key = '_security.secured_area.target_path'; #where "main" is your firewall name

        //check if the referrer session key has been set 
        if ($this->container->get('session')->has($key)) {
            //set the url based on the link they were trying to access before being authenticated
            $url = $this->container->get('session')->get($key);

            //remove the session key
            $this->container->get('session')->remove($key);
            return new RedirectResponse($url);   
        }      
        
        if ($referer_url
                && $referer_url != $this->container->get('router')->generate('login', array(), true)
                && $referer_url != $this->container->get('router')->generate('registration-form', array(), true))
        {
            return new RedirectResponse($referer_url);   
        }
        else
        {
            return new RedirectResponse($this->container->get('router')->generate('homepage'));
        }
    }
}
