<?php
namespace AppBundle\Model;

/**
 * Description of ScriptConverter
 *
 * @author lucas
 */
class ScriptConverterModel
{            
    private $container;
    
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }                      
    
    public function deleteScriptConversion(\AppBundle\Entity\ScriptConverter $conversion)
    {
        $dao = $this->container->get("dao.scriptconverter");
        $dao->deleteScriptConversion($conversion);
        
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/w2share/".$conversion->getHash());
        rmdir(__DIR__."/../../../web/uploads/documents/w2share/".$conversion->getHash());        
    }
    
    public function addWorkflow(\AppBundle\Entity\Workflow $workflow)
    {     
        $workflow_model = $this->container->get('model.workflow');
        $workflow_model->addWorkflow($workflow);
        $this->addWorkflowIntoConversion($workflow);
    }
    
    public function addWorkflowIntoConversion(\AppBundle\Entity\Workflow $workflow)
    {
        $dao = $this->container->get("dao.scriptconverter");        
        $conversion = $dao->findOneScriptConversionByHash($workflow->getHash());
        $conversion->setWorkflow($workflow);
        $dao->updateScriptConversion($conversion);
    }        
    
    public function createGraphServiceResponse($data)
    {
        $code = $data['code'];
        $language = $data['language'];
        $properties = $data['properties'];        
        
        $converter = new \AppBundle\Entity\ScriptConverter();
        $converter->setHash('web');        
        $converter->setScriptLanguage($language);
        $converter->setScriptCode($code);        
        $converter->setGraphProperties($properties);
        $converter->createGraph();
        
        $json = array(
            'svg' => file_get_contents($converter->getAbstractWorkflowFilepath()),
            'error' => null,
            'dot' => null,
            'skeleton' => null
            );
        
        return json_encode($json);       
    }                
    
    public function clearUploads ()
    {            
        \AppBundle\Utils\Utils::unlinkr(__DIR__."/../../../web/uploads/documents/w2share");
    }               
}