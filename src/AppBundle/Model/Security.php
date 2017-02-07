<?php
namespace AppBundle\Model;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AppBundle\Utils\Utils;

class Security
{    
    private $driver;   
    
    private $container;
    
    public function __construct($driver, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
        $this->driver = $driver;        
    }
    
    public function saveUser($user)
    {
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($password);
        
        $uri = Utils::convertNameToUri("Quality Dimension", $user->getName());
        $user->setUri($uri);
        
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('security')."> 
            { 
                <".$user->getUri()."> a <foaf:Person>.
                <".$user->getUri()."> <foaf:mbox> '".$user->getEmail()."'.
                <".$user->getUri()."> <foaf:name> '".$user->getName()."'.
                <".$user->getUri()."> <foaf:homepage> '".$user->getHomepage()."'.
                <".$user->getUri()."> <w2share:hasPassword> '".$user->getPassword()."'.
                <".$user->getUri()."> <w2share:hasSalt> '".$user->getSalt()."'.
            }
        }";  

        return $this->driver->getResults($query);
    }
    
    public function loadUserByUsername($username)
    {
        $user = new \AppBundle\Entity\Person(); 
        
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('security')."> 
            { 
                ?uri a <foaf:Person>;
                <foaf:name> ?name;
                <foaf:mbox> '".$username."';
                <foaf:homepage> ?homepage;
                <w2share:hasPassword> ?password;
                <w2share:hasSalt> ?salt.
            }
        }";   
        
        $user_array = $this->driver->getResults($query);
        
        try 
        {
            $user->setUri($user_array[0]['uri']['value']);
            $user->setName($user_array[0]['name']['value']);
            $user->setEmail($username);
            $user->setPassword($user_array[0]['password']['value']);
            $user->setHomepage($user_array[0]['homepage']['value']);
            $user->setSalt($user_array[0]['salt']['value']);
        } 
        catch (\Symfony\Component\Debug\Exception\ContextErrorException $ex) 
        {
            if ($username == "lucas.carvalho@ic.unicamp.br")
            {
                $user->setName("Lucas Carvalho");
                $user->setEmail('lucas.carvalho@ic.unicamp.br');
                $user->setPassword('123123');                
                $user->setHomepage('http://www.lis.ic.unicamp.br/~lucascarvalho/');
            }
            else
            {
                $user->setName("Joana Malaverri");
                $user->setEmail('joana@ic.unicamp.br');
                $user->setPassword('joana');
                $user->setHomepage('http://www.lis.ic.unicamp.br/~joana/');
            }
            
            $this->saveUser($user);
        }                                
        
        return $user;
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('security').">";        
        return $this->driver->getResults($query);                  
    }
}
