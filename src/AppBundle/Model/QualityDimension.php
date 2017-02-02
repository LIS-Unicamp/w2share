<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Model;
use AppBundle\Utils\Utils;
/**
 * Description of QualityFlow
 *
 * @author joana
 */
class QualityDimension {
    private $driver;
    
    private $prefix = "
    prefix dc:  <http://purl.org/dc/elements/1.1/>
    prefix prov:  <http://www.w3.org/ns/prov#>
    prefix cnt:  <http://www.w3.org/2011/content#>
    prefix foaf:  <http://xmlns.com/foaf/0.1/>
    prefix dcmitype:  <http://purl.org/dc/dcmitype/>
    prefix wfprov:  <http://purl.org/wf4ever/wfprov#>
    prefix dcam:  <http://purl.org/dc/dcam/>
    prefix xml:  <http://www.w3.org/XML/1998/namespace>
    prefix vs:  <http://www.w3.org/2003/06/sw-vocab-status/ns#>
    prefix dcterms:  <http://purl.org/dc/terms/>
    prefix rdfs:  <http://www.w3.org/2000/01/rdf-schema#>
    prefix wot:  <http://xmlns.com/wot/0.1/>
    prefix wfdesc:  <http://purl.org/wf4ever/wfdesc#>
    prefix dct:  <http://purl.org/dc/terms/>
    prefix tavernaprov:  <http://ns.taverna.org.uk/2012/tavernaprov/>
    prefix owl:  <http://www.w3.org/2002/07/owl#>
    prefix xsd:  <http://www.w3.org/2001/XMLSchema#>
    prefix rdf:  <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    prefix skos:  <http://www.w3.org/2004/02/skos/core#>
    prefix scufl2:  <http://ns.taverna.org.uk/2010/scufl2#>
    prefix oa:      <http://www.w3.org/ns/oa#>
    prefix w2share: <http://www.lis.ic.unicamp.br/w2share/qualityflow#>";
    
    public function __construct($driver)
    {
        $this->driver = $driver;
    }
    
    public function clearDB ()
    {
        $this->em->createQuery('DELETE FROM AppBundle:QualityDimension')->execute();
    }
    
    public function insertQualityDimension(\AppBundle\Entity\QualityDimension $qd) 
    { 
        $uri = Utils::convertNameToUri("Quality Dimension", $qd->getName());
        $qd->setUri($uri);
        $query = $this->prefix.
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
            { 
                <".$qd->getUri()."> a <w2share:QualityDimension>.
                <".$qd->getUri()."> <w2share:qdName> '".$qd->getName()."'.
                <".$qd->getUri()."> <w2share:valuetype> '".$qd->getValueType()."'.
                <".$qd->getUri()."> <rdfs:description> '".$qd->getDescription()."'.
            }
        }";  

        return $this->driver->getResults($query);
        
    }
    
    public function findOneQualityDimension($uri) 
    { 
        $query = $this->prefix.
        "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydimension')."> 
            { 
                <".$uri."> a <w2share:QualityDimension>.
                <".$uri."> <w2share:qdName> ?name.
                <".$uri."> <w2share:valuetype> ?valueType.
                <".$uri."> <rdfs:description> ?description.
            }
        }";     
        
        $quality_dimension = $this->driver->getResults($query);
        
        $qualityDimension = new \AppBundle\Entity\QualityDimension();
        $qualityDimension->setUri($uri);
        $qualityDimension->setName($quality_dimension[0]['name']['value']);
        $qualityDimension->setDescription($quality_dimension[0]['description']['value']);
        $qualityDimension->setValueType($quality_dimension[0]['valueType']['value']);
        return $qualityDimension;
        
    }
    
    public function findAllQualityDimension() {
        $query = "SELECT * FROM AppBundle:QualityDimension";
        return $this->driver->getResults($query);
    }
    
    public function updateQualityDimension(\AppBundle\Entity\QualityDimension $qd) {
        
        $query = "UPDATE AppBundle:QualityDimension tqd"
                . "SET tqd->name = ?, tqd->description = ?, tqd->type = ?"
                . "WHERE tqd->name = $qd->name";
        $query->setParameter(3, $qd->name, $qd->description, $qd->type);
        return $this->driver->getResults($query);  
        
    }
    
}
