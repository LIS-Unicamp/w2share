<?php
namespace AppBundle\Model;

/**
 * Description of ScriptConverter
 *
 * @author lucas
 */
class ScriptConverter
{    
    private $driver;
        
    public function __construct($driver)
    {
        $this->driver = $driver;
    }      
    
    public function insertScriptConversion(\AppBundle\Entity\ScriptConverter $converter, $user) 
    { 
        $uri = \AppBundle\Utils\Utils::convertNameToUri("Script Converter", $converter->getHash());
        $converter->setUri($uri);
        $query = 
        "INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('scriptconverter')."> 
            { 
                <".$uri."> a <w2share:ScriptConversion>.
                <".$uri."> <w2share:hash> '".$converter->getHash()."'.
                <".$uri."> <w2share:scriptLanguage> '".$converter->getScriptLanguage()."'.
                <".$uri."> <w2share:createdAt> '".$converter->getCreatedAt()->format(\DateTime::ISO8601)."'.
                <".$uri."> <w2share:updatedAt> '".$converter->getUpdatedAt()->format(\DateTime::ISO8601)."'.
                <".$uri."> <dc:creator> <".$user->getUri().">. 
            }
        }"; 

        return $this->driver->getResults($query);        
    }
    
    public function findOneScriptConversion($uri) 
    { 
        $query = 
        "SELECT * WHERE        
        { 
            <".$uri."> a <w2share:ScriptConversion>.
            <".$uri."> <w2share:hash> ?hash.
            <".$uri."> <w2share:scriptLanguage> ?scriptLanguage.
            <".$uri."> <w2share:createdAt> ?createdAt.
            <".$uri."> <w2share:updatedAt> ?updatedAt.
            <".$uri."> <dc:creator> ?creator. 
            ?creator <foaf:name> ?name.
        }";   
        
        $result_array = $this->driver->getResults($query);
        
        if (count($result_array) > 0)
        {
            $converter = new \AppBundle\Entity\ScriptConverter();
            $converter->setUri($result_array[0]['uri']['value']);
            $converter->setHash($result_array[0]['hash']['value']);
            $converter->setCreatedAt($result_array[0]['createdAt']['value']);
            $converter->setUpdatedAt($result_array[0]['updatedAt']['value']);
            $converter->setScriptLanguage($result_array[0]['scriptLanguage']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[$i]['creator']['value']);
            $creator->setName($result_array[$i]['name']['value']);
            $converter->setCreator($creator);
                
            return $converter;    
        }
        return null;
    }
    
    public function findScriptConversions() 
    { 
        $query = 
        "SELECT * WHERE        
        { 
            ?uri a <w2share:ScriptConversion>.
            ?uri <w2share:hash> ?hash.
            ?uri <w2share:scriptLanguage> ?scriptLanguage.
            ?uri <w2share:createdAt> ?createdAt.
            ?uri <w2share:updatedAt> ?updatedAt.
            ?uri <dc:creator> ?creator. 
            ?creator <foaf:name> ?name.
        }";   
        
        $result_array = $this->driver->getResults($query);
        $conversion = array();
        
        for ($i=0; $i < count($result_array); $i++)
        {
            $converter = new \AppBundle\Entity\ScriptConverter();
            $converter->setHash($result_array[$i]['hash']['value']);
            $converter->setUri($result_array[$i]['uri']['value']);
            $converter->setCreatedAt($result_array[$i]['createdAt']['value']);
            $converter->setUpdatedAt($result_array[$i]['updatedAt']['value']);
            $converter->setScriptLanguage($result_array[$i]['scriptLanguage']['value']);
            
            $creator = new \AppBundle\Entity\Person();
            $creator->setUri($result_array[$i]['creator']['value']);
            $creator->setName($result_array[$i]['name']['value']);
            $converter->setCreator($creator);
                
            $conversion[] = $converter;    
        }
        return $conversion;
    }
    
    public function clearGraph()
    {
        $query = "CLEAR GRAPH <".$this->driver->getDefaultGraph('scriptconverter').">";        
        return $this->driver->getResults($query);              
    }
    
    public function clearUploads ()
    {            
        $this->unlinkr(__DIR__."/../../../web/uploads/documents/w2share");
    }
    
    function unlinkr($dir, $pattern = "*") {
        // find all files and folders matching pattern
        $files = glob($dir . "/$pattern"); 

        //interate thorugh the files and folders
        foreach($files as $file){ 
        //if it is a directory then re-call unlinkr function to delete files inside this directory     
            if (is_dir($file) and !in_array($file, array('..', '.')))  {
                $this->unlinkr($file, $pattern);
                //remove the directory itself
                rmdir($file);
            } else if(is_file($file) and ($file != __FILE__)) {
                // make sure you don't delete the current script
                unlink($file); 
            }
        }
    }         
}
