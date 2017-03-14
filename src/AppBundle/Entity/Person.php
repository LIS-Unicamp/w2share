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
    private $homepage;

    /**
     * @var string
     */
    private $salt;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $email;
    
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
    
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
        
        return $this;
    }
    
    public function getHomepage()
    {
        return $this->homepage;
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
            $this->homepage,
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
            $this->homepage,
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
        return $user->getUri() == $this->uri;
    }

    public function __toString() {
        return $this->name;
    }
    /**
     * @var string
     */
    private $organization;

    /**
     * @var string
     */
    private $description;


    /**
     * Set organization
     *
     * @param string $organization
     * @return Person
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    
        return $this;
    }

    /**
     * Get organization
     *
     * @return string 
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Person
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $created_at;

    /**
     * @var string
     */
    private $updated_at;


    /**
     * Set country
     *
     * @param string $country
     * @return Person
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Person
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Person
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set created_at
     *
     * @param string $createdAt
     * @return Person
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return string 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param string $updatedAt
     * @return Person
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return string 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $quality_metric;


    /**
     * Add quality_metric
     *
     * @param \AppBundle\Entity\QualityMetric $qualityMetric
     * @return Person
     */
    public function addQualityMetric(\AppBundle\Entity\QualityMetric $qualityMetric)
    {
        $this->quality_metric[] = $qualityMetric;
    
        return $this;
    }

    /**
     * Remove quality_metric
     *
     * @param \AppBundle\Entity\QualityMetric $qualityMetric
     */
    public function removeQualityMetric(\AppBundle\Entity\QualityMetric $qualityMetric)
    {
        $this->quality_metric->removeElement($qualityMetric);
    }

    /**
     * Get quality_metric
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQualityMetric()
    {
        return $this->quality_metric;
    }
}