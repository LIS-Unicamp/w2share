<?php
namespace AppBundle\Model;

/**
 * Description of YesWorkflow
 *
 * @author lucas
 */
class YesWorkflow
{    
    
    public function __construct()
    {
    }
    
    public function createGraph($yesworkflow)
    {        
        $command = "java -jar ".$yesworkflow->getUploadRootDir() . "/../../../../src/AppBundle/Utils/yesworkflow-0.2-SNAPSHOT-jar-with-dependencies.jar graph -c model.factsfile=" . $yesworkflow->getUploadRootDir()."/modelfacts.txt " . $yesworkflow->getScriptAbsolutePath() . " > " . $yesworkflow->getUploadRootDir() . "/wf.gv; /usr/local/bin/dot -Tpng " . $yesworkflow->getUploadRootDir() . "/wf.gv -o " . $yesworkflow->getUploadRootDir()."/wf.png";                              
        system($command);        
    }
}
