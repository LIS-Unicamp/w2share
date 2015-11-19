<?php
namespace AppBundle\Model;

/**
 * Description of Workflow
 *
 * @author lucas
 */
class Workflow 
{
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
    
    private $driver;
    protected $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $driver)
    {
        $this->driver = $driver;
        $this->em = $em;
    }
    
    public function clearDB ()
    {
        $this->em->createQuery('DELETE FROM AppBundle:Workflow')->execute();
    }
    
    public function clearUploads ($root_path)
    {       
        foreach(glob($root_path."/../web/uploads/documents/*.*") as $file)
        {            
            unlink($file);
        }
    }  
    
    public function processes($workflow)
    {
        // process information
        $query = "
            $this->prefix  
            SELECT DISTINCT * WHERE {GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/> {
                <$workflow> wfdesc:hasSubProcess ?process.
                ?process a wfdesc:Process.
                OPTIONAL { ?process rdfs:label ?label. }
                OPTIONAL { ?process dcterms:description ?description. }
                OPTIONAL { ?process prov:specializationOf ?workflow. }
            }}
            ";
        
        $query = $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');   
        return $query->_odbc_fetch_array2();
    }
    
    public function clearGraph()
    {
        $query1 = "CLEAR GRAPH <http://www.lis.ic.unicamp.br/~lucascarvalho/workflows/>";        
        $this->driver->_execute('CALL DB.DBA.SPARQL_EVAL(\'' . $query1 . '\', NULL, 0)');                  
    }
}
