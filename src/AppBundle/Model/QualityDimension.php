<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Model;

/**
 * Description of QualityFlow
 *
 * @author joana
 */
class QualityDimension {
    private $driver;
    protected $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $driver)
    {
        $this->driver = $driver;
        $this->em = $em;
    }
    
    public function clearDB ()
    {
        $this->em->createQuery('DELETE FROM AppBundle:QualityDimension')->execute();
    }
    
    public function insertQualityDimension(QualityDimension $qd) {
        $rsm = new ResultSetMapping();
        $query = $this->em->createNativeQuery('INSERT INTO AppBundle:QualityDimension'
                                               . ' VALUES (?, ?, ?)', $rsm);
        $query->setParameter(3, $qd->name, $qd->description, $qd->type);
        #$result = $query->getResult();
        
        return $this->driver->getResults($query);
    }
    
    public function findAllQualityDimension() {
        $query = "SELECT * FROM AppBundle:QualityDimension";
        return $this->driver->getResults($query);
    }
    
    public function updateQualityDimension(QualityDimension $qd) {
        $query = "UPDATE AppBundle:QualityDimension tqd"
                . "SET tqd->name = ?, tqd->description = ?, tqd->type = ?"
                . "WHERE tqd->name = $qd->name";
        $query->setParameter(3, $qd->name, $qd->description, $qd->type);
        return $this->driver->getResults($query);
        
    }
    
}
