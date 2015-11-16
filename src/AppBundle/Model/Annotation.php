<?php
namespace AppBundle\Model;

/**
 * Description of Provenance
 *
 * @author lucas
 */
class Annotation
{
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
    prefix scufl2:  <http://ns.taverna.org.uk/2010/scufl2#>";
    
    public function __construct($driver)
    {
        $this->driver = $driver;
    }
    
    public function ontology()
    {

    }
    
    public function insertAnnotation($subject, $property, $object)
    {
        $query = "INSERT DATA
        { 
            GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> 
            { 
              ".$subject." ".$property." ".$object." 
            } 
        }";
        $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)'); 
    }
}

