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
        
        $uri = Utils::convertNameToUri("Security", $user->getName());
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
                <".$user->getUri()."> <org:memberOf> [ a <org:Organization>; <skos:prefLabel> '".$user->getOrganization()."'].
                <".$user->getUri()."> <dc:description> '".$user->getDescription()."'.
            }
        }";  

        return $this->driver->getResults($query);
    }
    
    public function findAllUsers()
    {                
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('security')."> 
            { 
                ?uri a <foaf:Person>.
                ?uri <foaf:name> ?name.
                ?uri <foaf:mbox> ?email.
                ?uri <foaf:homepage> ?homepage.
                OPTIONAL { ?uri <org:memberOf> [ a <org:Organization>; <skos:prefLabel> ?organization]. }
                OPTIONAL { ?uri <dc:description> ?description. }
            }
        }";   
        
        $user_array = $this->driver->getResults($query);
        $users = array();
        
        for ($i = 0; $i < count($user_array); $i ++)
        {
            $user = new \AppBundle\Entity\Person(); 
            $user->setUri($user_array[$i]['uri']['value']);
            $user->setName($user_array[$i]['name']['value']);
            $user->setEmail($user_array[$i]['email']['value']);
            $user->setHomepage($user_array[$i]['homepage']['value']);
            if (array_key_exists('description', $user_array[$i]))
            {
                $user->setDescription($user_array[$i]['description']['value']);
            }
            if (array_key_exists('organization', $user_array[$i]))
            {
                $user->setOrganization($user_array[$i]['organization']['value']);
            }
            
            $users[] = $user;
        }                                        
        
        return $users;
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
                OPTIONAL { ?uri <org:memberOf> [ a <org:Organization>; <skos:prefLabel> ?organization]. }
                OPTIONAL { ?uri <dc:description> ?description. }
            }
        }";   
        
        $user_array = $this->driver->getResults($query);
        
        if (count($user_array) > 0)
        {
            $user->setUri($user_array[0]['uri']['value']);
            $user->setName($user_array[0]['name']['value']);
            $user->setEmail($username);
            $user->setPassword($user_array[0]['password']['value']);
            $user->setHomepage($user_array[0]['homepage']['value']);
            $user->setSalt($user_array[0]['salt']['value']);
            if (array_key_exists('description', $user_array[0]))
            {
                $user->setDescription($user_array[0]['description']['value']);
            }
            if (array_key_exists('organization', $user_array[0]))
            {
                $user->setOrganization($user_array[0]['organization']['value']);
            }
            
            return $user;
        } 
        
        return null;      
    }
    
    public function findUserByURI($uri)
    {
        $user = new \AppBundle\Entity\Person(); 
        
        $query = 
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('security')."> 
            { 
                <".$uri."> a <foaf:Person>;
                <foaf:name> ?name;
                <foaf:mbox> ?email;
                <foaf:homepage> ?homepage;
                <w2share:hasPassword> ?password;
                <w2share:hasSalt> ?salt.
                OPTIONAL { ?uri <org:memberOf> [ a <org:Organization>; <skos:prefLabel> ?organization]. }
                OPTIONAL { ?uri <dc:description> ?description. }
            }
        }";   
        
        $user_array = $this->driver->getResults($query);
        
        if (count($user_array) > 0)
        {
            $user->setUri($uri);
            $user->setName($user_array[0]['name']['value']);
            $user->setEmail($user_array[0]['email']['value']);
            $user->setPassword($user_array[0]['password']['value']);
            $user->setHomepage($user_array[0]['homepage']['value']);
            $user->setSalt($user_array[0]['salt']['value']);
            
            if (array_key_exists('description', $user_array[0]))
            {
                $user->setDescription($user_array[0]['description']['value']);
            }
            if (array_key_exists('organization', $user_array[0]))
            {
                $user->setOrganization($user_array[0]['organization']['value']);
            }
            
            return $user;
        } 
        
        return null;  
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('security').">";        
        return $this->driver->getResults($query);                  
    }
}
