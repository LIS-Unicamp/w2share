<?php

namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Pessoa
 */
class Person implements AdvancedUserInterface, EquatableInterface, \Serializable
{
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->salt = md5(uniqid(null, true));
    }
    
    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $salt;
    
    /**
     * @var string
     */
    private $name;
    
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @var string
     */
    private $uri;
    
    public function setUri($uri)
    {
        $this->uri = $uri;
        
        return $this;
    }
    
    public function getUri()
    {
        return $this->uri;
    }       
    
    public function setEmail($email)
    {
        $this->email = $email;
        
        return $this;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    

    /**
     * Set salt
     *
     * @param string $salt
     * @return Pessoa
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }   
    
    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return array('ROLE_ADMIN');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->uri,
            $this->email,
            $this->salt,
            $this->name,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->uri,
            $this->email,
            $this->salt,
            $this->name,
        ) = unserialize($serialized);
    }
    
     /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->email;
    }


    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
        
        return $this;
    }
    
    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }
    /**
     * @var string
     */
    private $roles;


    /**
     * Set roles
     *
     * @param string $roles
     * @return Pessoa
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function isEqualTo(UserInterface $user) {
        return true;
    }

}

