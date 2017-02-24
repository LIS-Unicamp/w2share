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
        $command = "java -jar ".$yesworkflow->getUploadRootDir() . "/../../../../src/AppBundle/Utils/yesworkflow-0.2.1.1-jar-with-dependencies.jar graph -c extract.comment='#' -c graph.layout=TB -c graph.view=COMBINED -c model.factsfile=" . $yesworkflow->getUploadRootDir()."/modelfacts.txt " . $yesworkflow->getScriptAbsolutePath() . " > " . $yesworkflow->getUploadRootDir() . "/wf.gv; /usr/local/bin/dot -Tpng " . $yesworkflow->getUploadRootDir() . "/wf.gv -o " . $yesworkflow->getUploadRootDir()."/wf.png";                              
        system($command);        
    }
    
    public function downloadWorkflow($root_path, $language)
    {        
        $python = $root_path."/../web/uploads/documents/yesscript/conversion.py";
        $script = $root_path."/../web/uploads/documents/yesscript/script.sh";
        $workflow = $root_path."/../web/uploads/documents/yesscript/workflow.t2flow";
        $image = $root_path."/../web/uploads/documents/yesscript/workflow.svg";
            
        $command_python = "java -jar ".$root_path."/../src/AppBundle/Utils/yesworkflow2taverna.jar ".$script." ".$language." ".$python;
        system($command_python);
        
        $command_taverna = $root_path."/../vendor/lucasaugustomcc/balcazapy/bin/balc ".$python." ".$workflow;
        system($command_taverna);
        
        $command_image = "ruby ".$root_path."/../src/AppBundle/Utils/script.rb ".$workflow." ".$image;   
        system($command_image);               
        
        return $workflow;
    }
}
