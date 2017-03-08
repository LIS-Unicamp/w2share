<?php
namespace AppBundle\Utils;

class RestAPI 
{       
    private $default_graph = "http://www.lis.ic.unicamp.br/w2share";
   
    private $env;
    
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
    prefix comp: <http://purl.org/DP/components#>
    prefix dep: <http://scape.keep.pt/vocab/dependencies#>
    prefix wf4ever: <http://purl.org/wf4ever/wf4ever#>
    prefix biocat: <http://biocatalogue.org/attribute/>
    prefix ro: <http://purl.org/wf4ever/ro#>
    prefix ore: <http://www.openarchives.org/ore/terms/>
    prefix w2share: <http://www.lis.ic.unicamp.br/w2share/qualityflow#>";
    
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->env = $container->get('kernel')->getEnvironment();
    }        
    
    public function getDomainSPAQL()
    {
        $prod_domain = "10.1.1.32:8890/sparql";
        $dev_domain = "localhost:8890/sparql";
    
        if ($this->env == "prod")
        {
            return $prod_domain;
        }
        
        return $dev_domain;
    }
    
    public function getDomain()
    {
        $prod_domain = "10.1.1.32";
        $dev_domain = "localhost";
    
        if ($this->env == "prod")
        {
            return $prod_domain;
        }
        
        return $dev_domain;
    }
    
    public function getDefaultGraph ($name = null)
    {
        if ($name)
        {
            return $this->default_graph."/".$name;
        }
        return $this->default_graph;
    }
    
    private function getQuery($query)
    {
        $format = 'json';
        //$default_graph = self::getDefaultGraph();
       
        $searchUrl = $this->getDomainSPAQL().'?'
          //.'default-graph-uri='.urlencode($default_graph)
          .'query='.urlencode($query)
          .'&format='.$format;
        
        $results = json_decode(self::request($searchUrl),true); 
        return $results;
    }
    
    public function getResults($query, $print = false)
    {       
       $results = self::getQuery($this->prefix.' '.$query);
       
       if ($print == true)
       {
           echo $this->prefix."\n".$query."\n";
           echo self::printArray($results);
       }

       return $results['results']['bindings'];
    }
    
    public function getSingleResult($query, $print = false)
    {       
        $results = self::getResults($query,$print);
        if (is_array($results) && count($results) > 0)
        {
            return $results[0];
        }
        else
        {
           return array();
        }
    }
    
    public function load($path)
    {
        // is curl installed?
        if (!function_exists('curl_init')){ 
            die('CURL is not installed!');
        }
        $basename = basename($path);
        $ch = curl_init("http://lucascarvalho:lucas2@".$this->getDomain()."/DAV/home/lucascarvalho/".$basename); 
        $file = fopen($path, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $file);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($path));
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-type: application/x-turtle',
          'Accept: text/html'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        fclose($file);
        return $response;
    }
    
    public function request($url){

       // is curl installed?
       if (!function_exists('curl_init')){ 
          die('CURL is not installed!');
       }

       // get curl handle
       $ch= curl_init();

       // set request url
       curl_setopt($ch, 
          CURLOPT_URL, 
          $url);

       // return response, don't print/echo
       curl_setopt($ch, 
          CURLOPT_RETURNTRANSFER, 
          true);

       /*
       Here you find more options for curl:
       http://www.php.net/curl_setopt
       */		

       $response = curl_exec($ch);

       curl_close($ch);

       return $response;
    }
    
    public function printArray($array, $spaces = "")
    {
        $retValue = "";

        if(is_array($array))
        {	
           $spaces = $spaces
              ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
           $retValue = $retValue."<br/>";
           foreach(array_keys($array) as $key)
           {
              $retValue = $retValue.$spaces
                 ."<strong>".$key."</strong>"
                 .self::printArray($array[$key], 
                    $spaces);
           }		
           $spaces = substr($spaces, 0, -30);
        }
        else 
        {
            $retValue = $retValue." - ".$array."<br/>";
        }
           
       return $retValue;
    }
}