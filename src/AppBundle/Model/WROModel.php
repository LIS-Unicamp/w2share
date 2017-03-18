<?php
namespace AppBundle\Model;

/**
 * Description of the Research Object model
 *
 * @author lucas
 */
class WROModel
{        
    private $container;
    
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }                          
    
    public function addWRO(\AppBundle\Entity\WRO $wro)
    {
        $this->unzipWROBundle($wro);
        $this->findManifest($wro);
        //$this->loadFiles($wro);
        $this->saveWROHash($wro);        
    }          
    
    private function findManifest(\AppBundle\Entity\WRO $wro)
    {
        if (file_exists($this->getWRODirPath($wro)."/.ro/manifest.json"))
        {
            $this->loadManifestJSON($wro); 
        }
        else if (file_exists($this->getWRODirPath($wro)."/.ro/manifest.rdf"))
        {            
            $this->loadManifestRDF($wro);
        }
    }
    
    private function loadManifestJSON(\AppBundle\Entity\WRO $wro)
    {
        $str = file_get_contents($this->getWRODirPath($wro)."/.ro/manifest.json");
        $manifest_data = json_decode($str, true); // decode the JSON into an associative array

        foreach ($manifest_data['aggregates'] as $aggregate)
        {
            $aggregate['folder'].$aggregate['file'];
            if (in_array('mediatype', $aggregate))
            {
                $aggregate['mediatype'];
            }
        }    
        
        foreach ($manifest_data['annotations'] as $aggregate)
        {
            $aggregate['about'].$aggregate['content'];
            if (in_array('annotation', $aggregate))
            {
                $aggregate['annotation'];
            }
            
            if ($aggregate['content'] == '/workflow.wfbundle')
            {
                $this->unzipWFBundle($wro, $aggregate['content']);
            }
        } 
    }
    
    private function loadManifestRDF(\AppBundle\Entity\WRO $wro)
    {
        $path_url = $this->getWRODirPath($wro)."/.ro/manifest.rdf";
        
        \EasyRdf_Namespace::set('ro', 'http://purl.org/wf4ever/ro#');
        \EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
        \EasyRdf_Namespace::set('ore', 'http://www.openarchives.org/ore/terms/');

        $graph = new \EasyRdf_Graph();
        $graph->parseFile($path_url);
        //print_r($graph);
        $resource = $graph->resourcesMatching('ore:aggregates');
        $aggregates = $graph->AllResources($resource[0],'ore:aggregates');
        //echo($resource);
        foreach ($aggregates as $aggregate)
        {
            $aggregate.$aggregate->type();
        }
        exit;
    }
    
    private function unzipWFBundle(\AppBundle\Entity\WRO $wro, $wfbundle_filename)
    {            
        $wfbundle_path = $this->getWRODirPath($wro).$wfbundle_filename;
        $zip = new \ZipArchive; 
        if ($zip->open($wfbundle_path))
        { 
            try 
            {
                $zip->extractTo($this->getWRODirPath($wro)); 
                $zip->close(); 
            }
            catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
            {
                $e->getMessage();
            }
            
        }
        @unlink($wfbundle_path);
    }
    
    private function unzipWROBundle(\AppBundle\Entity\WRO $wro)
    {
            
        $zip = new \ZipArchive; 
        if ($zip->open($wro->getWROAbsolutePath()))
        { 
            $zip->extractTo($this->getWRODirPath($wro)); 
            $zip->close(); 
        }
        unlink($wro->getWROAbsolutePath());
    }
    
    private function getWRODirPath(\AppBundle\Entity\WRO $wro)
    {
        $wroot_path = $this->container->get('kernel')->getRootDir();

        return $wroot_path."/../web/uploads/documents/wro/".$wro->getHash();
    }                   
    
    public function clearUploads()
    {
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/wro");
    }         
    
    public function createWRO(\AppBundle\Entity\ScriptConverter $conversion)
    {
        $wro = new \AppBundle\Entity\WRO();        
        $wro->setHash($conversion->getHash());
        $wro_uri = $uri = \AppBundle\Utils\Utils::convertNameToUri("Workflow Research Object", $wro->getHash());
        $wro->setUri($wro_uri);
        $wro->setCreator($conversion->getCreator());
        $wro->setWorkflow($conversion->getWorkflow());
        $wro->setScriptConversion($conversion);
        $this->addResources($wro);
        //$wro->addResource($resources);
        $this->saveWRO($wro);
        $this->saveWROScriptConversion($wro);
        $this->createWROScript($wro);
    }
    
    public function addResources(\AppBundle\Entity\WRO $wro)
    {
        if ($wro->getWorkflow())
        {
            $wro->addResourceBuilder($wro->getWorkflow()->getUri(), basename($wro->getWorkflow()->getWorkflowAbsolutePath()), '', 'Workflow specification', 'wfdesc:Workflow');
        }
        $conversion = $wro->getScriptConversion();
        if ($wro->getWorkflow()->getWorkflowRuns())
        {
            $file_path = $wro->getScriptConversion()->getWorkflow()->getProvenanceAbsolutePath();
            $wro->addResourceBuilder(basename($file_path), basename($file_path), '', 'Provenance Data', 'wfprov:WorkflowRun');
        }
        if ($conversion)
        {
            $file_path = $wro->getScriptConversion()->getScriptFilepath();
            $wro->addResourceBuilder(basename($file_path), basename($file_path), '', 'Script', 'wf4ever:Script');
        }
    }        
    
    public function createWROScript(\AppBundle\Entity\WRO $wro)
    {
        //TODO: add rdf:type wf4ever:WorkflowResearchObject
        $code = "#!/bin/bash                   
                cd ".$wro->getUploadRootDir()."/../
                ROBASE=\"wro\"

                ro config -v \
                  -b ".'$ROBASE'." \
                  -r http://sandbox.wf4ever-project.org/rodl/ROs/ \
                  -t \"".$wro->getHash()."\" \
                  -n \"Lucas\" \
                  -e \"lucas.carvalho@ic.unicamp.br\"

                mkdir  ".'$ROBASE'."/test-create-RO

                rm -rf ".'$ROBASE'."/test-create-RO/.ro
                    
                rsync -aP --exclude=".'$ROBASE'." --exclude=create-wro.sh --exclude=conversion.py --exclude=wf.gv . ".'$ROBASE'."/test-create-RO

                ro create -v \"Reproducible WRO\" -d ".'$ROBASE'."/test-create-RO -i RO-id-testCreate

                ro add -v -a ".'$ROBASE'."/test-create-RO -d ".'$ROBASE'."/test-create-RO\n";

        foreach ($wro->getResources() as $resource)
        {
            $code .= "ro annotate -v ".'$ROBASE'."/test-create-RO/".$resource->getFolder()."/".$resource->getFilename()." rdf:type \"".$resource->getType()."\"\n";
            $code .= "ro annotate -v ".'$ROBASE'."/test-create-RO/".$resource->getFolder()."/".$resource->getFilename()." title \"".$resource->getDescription()."\"\n";
        }
        $code .= 'cd '.$wro->getUploadRootDir().'
                echo -n application/vnd.wf4ever.robundle+zip > '.$wro->getUploadRootDir().'/mimetype

                zip -0 -X '.$wro->getWROAbsolutePath().' mimetype  

                zip -X -r '.$wro->getWROAbsolutePath().' . -x mimetype';
        
        $fs = new \Symfony\Component\Filesystem\Filesystem();    
        $fs->mkdir($wro->getUploadRootDir());
        $fs->dumpFile($wro->getWROScriptAbsolutePath(), $code);
        exec('sh '.$wro->getWROScriptAbsolutePath());
    }
}