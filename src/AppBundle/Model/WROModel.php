<?php
namespace AppBundle\Model;

/**
 * Description of the Research Object model
 *
 * @author lucas
 */
class WROModel
{        
    private $driver;
    
    private $container;
    
    public function __construct($driver, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
        $this->driver = $driver;
    }                           
    
    public function addWRO(\AppBundle\Entity\WRO $wro)
    {
        $this->unzipWROBundle($wro);
        $this->findManifest($wro);
        //$this->loadFiles($wro);
        $this->createWROUploaded($wro);
        exit;
    }          
    
    private function findManifest(\AppBundle\Entity\WRO $wro)
    {
        if (file_exists($wro->getUploadRootDir()."/.ro/manifest.json"))
        {
            $this->loadManifestJSON($wro); 
        }
        else if (file_exists($wro->getUploadRootDir()."/.ro/manifest.rdf"))
        {            
            $this->loadManifestRDF($wro);
        }
    }
    
    private function loadManifestJSON(\AppBundle\Entity\WRO $wro)
    {
        $str = file_get_contents($wro->getUploadRootDir()."/.ro/manifest.json");
        $manifest_data = json_decode($str, true); // decode the JSON into an associative array

        foreach ($manifest_data['aggregates'] as $aggregate)
        {
            echo $aggregate['file']."\n";
            if (key_exists('mediatype', $aggregate))
            {
                echo $aggregate['mediatype']."\n";
            }            
        }    
        
        foreach ($manifest_data['annotations'] as $aggregate)
        {
            echo $aggregate['about'].$aggregate['content']."\n";
            if (key_exists('annotation', $aggregate))
            {
                echo $aggregate['annotation']."\n";
            }
            
            if ($aggregate['content'] == '/workflow.wfbundle')
            {
                $this->unzipWFBundle($wro, $aggregate['content']);
            }
            
            if ($aggregate['content'] == "/workflowrun.prov.ttl")
            {
                $this->driver->load($wro->getUploadDir()."/wro/workflowrun.prov.ttl");
            }
            
            if ($aggregate['content'] == "/.ro/annotations/workflow.wfdesc.ttl")
            {
                $model = $this->container->get('model.workflow');
                $workflow = new \AppBundle\Entity\Workflow();
                $this->driver->load($wro->getUploadDir()."/wro/.ro/annotations/workflow.wfdesc.ttl");
                $model->getURIFromWfdesc($workflow, $wro->getUploadDir()."/wro/.ro/annotations/workflow.wfdesc.ttl");
                $model->saveWorkflowHash($workflow);
                echo "loaded!\n";
                
                $md5 = \AppBundle\Utils\Utils::extractMD5($workflow->getUri());
                $workflow_path = $wro->getUploadRootDir()."/history/".$md5.".t2flow";
                
                $fs = new \Symfony\Component\Filesystem\Filesystem();    
                //$fs->copy($workflow_path, $workflow->getWorkflowAbsolutePath());
                
                $workflow->createWorkflowPNG();
                $wro->setWorkflow($workflow);
            }
        } 
    }
    
    public function createWROUploaded(\AppBundle\Entity\WRO $wro)
    {
        $dao = $this->container->get('dao.wro');
        
        $workflow = $wro->getWorkflow();
        $wro->setHash($workflow->getHash());
        $wro_uri = $uri = \AppBundle\Utils\Utils::convertNameToUri("Workflow Research Object", $wro->getHash());
        $wro->setUri($wro_uri);
        //$wro->setCreator($conversion->getCreator());
        $this->addDefaultResources($wro);
        $dao->saveWRO($wro);
        $dao->saveWROResources($wro);
        $this->createWROScript($wro);
    }
    
    private function loadManifestRDF(\AppBundle\Entity\WRO $wro)
    {
        $path_url = $wro->getUploadRootDir()."/.ro/manifest.rdf";
        
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
            echo $aggregate.$aggregate->type();
        }
        exit;
    }
    
    private function unzipWFBundle(\AppBundle\Entity\WRO $wro, $wfbundle_filename)
    {            
        $wfbundle_path = $wro->getUploadRootDir().$wfbundle_filename;
        echo $wfbundle_path." - path\n";
        $zip = new \ZipArchive; 
        if ($zip->open($wfbundle_path))
        { 
            try 
            {
                $zip->extractTo($wro->getUploadRootDir()); 
                $zip->close(); 
            }
            catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
            {
                echo $e->getMessage();
            }
            
        }
        //@unlink($wfbundle_path);
    }
    
    private function unzipWROBundle(\AppBundle\Entity\WRO $wro)
    {            
        $zip = new \ZipArchive; 
        echo $wro->getWROAbsolutePath();
        if ($zip->open($wro->getWROAbsolutePath()))
        { 
            $zip->extractTo($wro->getUploadRootDir()); 
            $zip->close(); 
        }
        unlink($wro->getWROAbsolutePath());
    }    
    
    /**
     * @param type Workflow
     */
    public function deleteWRO(\AppBundle\Entity\WRO $wro)
    {
        $wro->removeUpload(); 
        $dao = $this->container->get('dao.wro');
        $dao->deleteWRO($wro);
    }
    
    public function resetData()
    {
        $dao = $this->container->get('dao.wro');        
        $dao->clearGraph();        
        $dao->resetWROScriptConversion();
        $this->clearUploads();        
    }
    
    public function clearUploads()
    {
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/wro");
    }         
    
    public function createWRO(\AppBundle\Entity\ScriptConverter $conversion)
    {
        $dao = $this->container->get('dao.wro');
        
        $wro = new \AppBundle\Entity\WRO();        
        $wro->setHash($conversion->getHash());
        $wro_uri = $uri = \AppBundle\Utils\Utils::convertNameToUri("Workflow Research Object", $wro->getHash());
        $wro->setUri($wro_uri);
        $wro->setCreator($conversion->getCreator());
        $wro->setWorkflow($conversion->getWorkflow());
        $wro->setScriptConversion($conversion);
        $this->addDefaultResources($wro);
        //$wro->addResource($resources);
        $dao->saveWRO($wro);
        $dao->saveWROScriptConversion($wro);
        $dao->saveWROResources($wro);
        $this->createWROScript($wro);
    }
    
    public function addDefaultResources(\AppBundle\Entity\WRO $wro)
    {
        if ($wro->getWorkflow())
        {
            $wro->addResourceBuilder($wro->getWorkflow()->getUri(), basename($wro->getWorkflow()->getWorkflowAbsolutePath()), '', 'Workflow specification', 'wfdesc:Workflow');
            $wro->addResourceBuilder($wro->getWorkflow()->getUri(), basename($wro->getWorkflow()->getWorkflowImageFilepath()), '', 'Workflow Image', 'wf4ever:Image');
        }
        $conversion = $wro->getScriptConversion();
        if ($conversion && $conversion->getWorkflow()->getWorkflowRuns())
        {
            $file_path = $conversion->getWorkflow()->getProvenanceAbsolutePath();
            $wro->addResourceBuilder(basename($file_path), basename($file_path), '', 'Provenance Data', 'wfprov:WorkflowRun');
        }
        if ($conversion)
        {
            $file_path = $conversion->getScriptFilepath();
            $wro->addResourceBuilder(basename($file_path), basename($file_path), '', 'Script', 'wf4ever:Script');
            
            $file_path = $conversion->getAbstractWorkflowFilepath();
            $wro->addResourceBuilder(basename($file_path), basename($file_path), '', 'Workflow-like view of the script', 'wf4ever:Image');
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
                    
                rsync -aP --exclude=".'$ROBASE'." --exclude=".basename($wro->getWROAbsolutePath())." --exclude=create-wro.sh --exclude=debug.log --exclude=conversion.py --exclude=wf.gv . ".'$ROBASE'."/test-create-RO

                ro create -v \"Reproducible WRO\" -d ".'$ROBASE'."/test-create-RO -i RO-id-testCreate

                ro add -v -a ".'$ROBASE'."/test-create-RO -d ".'$ROBASE'."/test-create-RO
                
                ro annotate -v ".'$ROBASE'."/test-create-RO/ rdf:type \"wf4ever:WorkflowResearchObject\"\n";

        foreach ($wro->getResources() as $resource)
        {
            $code .= "ro annotate -v ".'$ROBASE'."/test-create-RO/".$resource->getFolder()."/".$resource->getFilename()." rdf:type \"".$resource->getType()."\"\n";
            $code .= "ro annotate -v ".'$ROBASE'."/test-create-RO/".$resource->getFolder()."/".$resource->getFilename()." title \"".$resource->getDescription()."\"\n";
        }
        $code .= 'cd '.$wro->getUploadRootDir().'/../

                zip -X -r '.$wro->getWROAbsolutePath().' wro/*';
        
        $fs = new \Symfony\Component\Filesystem\Filesystem();    
        $fs->mkdir($wro->getUploadRootDir());
        $fs->dumpFile($wro->getWROScriptAbsolutePath(), $code);
        $command = 'sh '.$wro->getWROScriptAbsolutePath().' > '.$wro->getUploadRootDir().'/../debug.log 2>&1';
        shell_exec($command);
        
        //unlink($wro->getWROScriptAbsolutePath());
        \AppBundle\Utils\Utils::unlinkr($wro->getUploadRootDir());
        $fs->mkdir($wro->getUploadRootDir());        
    }
}