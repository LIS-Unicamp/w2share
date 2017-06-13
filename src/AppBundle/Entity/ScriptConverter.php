<?php

namespace AppBundle\Entity;
use Symfony\Component\Filesystem\Filesystem;
/**
 * YesScript
 */
class ScriptConverter
{                         
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \AppBundle\Entity\WRO
     */
    private $wro;

    /**
     * @var \AppBundle\Entity\Person
     */
    private $creator;
    
    public function __construct() 
    {
        $this->hash = sha1(uniqid(mt_rand(), true));
        $this->created_at = new \Datetime();
        $this->updated_at = new \Datetime();
    }


    /**
     * Set uri
     *
     * @param string $uri
     * @return ScriptConverter
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    
        return $this;
    }

    /**
     * Get uri
     *
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ScriptConverter
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ScriptConverter
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return ScriptConverter
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    
        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ScriptConverter
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }   

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\Person $creator
     * @return ScriptConverter
     */
    public function setCreator(\AppBundle\Entity\Person $creator = null)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\Person 
     */
    public function getCreator()
    {
        return $this->creator;
    }
    
    public function setScriptCode($code)
    {
        $fs = new Filesystem();           
        $fs->dumpFile($this->getScriptFilepath(), $code);
    }
    
    public function getScriptCode()
    {        
        return file_get_contents($this->getScriptFilepath());
    }
    
    /**
     * @var string
     */
    private $script_language;


    /**
     * Set language
     *
     * @param string $language
     * @return ScriptConverter
     */
    public function setScriptLanguage($language)
    {
        $this->script_language = $language;
    
        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getScriptLanguage()
    {
        return $this->script_language;
    }
    
    /**
     * Get language
     *
     * @return string 
     */
    public function getScriptLanguageEnum()
    {
        switch ($this->script_language)
        {
            case "sh":  return "Bash";
            case "r":   return "R";
            case "py":  return "Python";
        }                
        return $this->script_language;
    }
    
    public function createGraph()
    {        
        $command = "java -jar ". __DIR__ . "/../../../src/AppBundle/Utils/yesworkflow-0.2.1.1-jar-with-dependencies.jar graph ".$this->getGraphProperties(). " " . $this->getScriptFilepath() . " > " . $this->getUploadRootDir() . "/wf.gv; /usr/bin/dot -Tsvg " . $this->getUploadRootDir() . "/wf.gv -o " . $this->getAbstractWorkflowFilepath();                              
        system($command);        
    }
    
    public function getDraftWorkflowT2FlowFilepath()
    {
        return $this->getUploadRootDir()."/draft-workflow.t2flow";
    }
    
    public function getDraftWorkflowT2FlowFile()
    {                                 
        return file_get_contents($this->getDraftWorkflowT2FlowFilepath());
    }        
    
    public function getScriptFilepath()
    {
        return $this->getUploadRootDir()."/script.".$this->getScriptExtension();
    }
    
    public function getScriptFileWebPath()
    {
        return $this->getWebPath()."/script.".$this->getScriptExtension();
    }
    
    public function getAbstractWorkflowFilepath()
    {
        return $this->getUploadRootDir()."/abstract-workflow.svg";
    }
    
    public function getAbstractWorkflowFileWebPath()
    {
        return $this->getWebPath()."/abstract-workflow.svg";
    }
    
    public function getAbstractWorkflowFile()
    {
        return file_get_contents($this->getAbstractWorkflowFilepath());
    }
    
    public function createWorkflow()
    {
        $python = $this->getUploadRootDir()."/conversion.py";
        $script = $this->getScriptFilepath();
        $workflow = $this->getDraftWorkflowT2FlowFilepath();
        $image = $this->getDraftWorkflowImageFilePath();
            
        $command_python = "java -jar ".__DIR__."/../../../src/AppBundle/Utils/yesworkflow2taverna.jar ".$script." ".$this->getScriptLanguage()." ".$python;
        system($command_python);
        
        $command_taverna = __DIR__."/../../../vendor/lucasaugustomcc/balcazapy/bin/balc ".$python." ".$workflow;
        system($command_taverna);
        
        $command_image = "ruby ".__DIR__."/../../../src/AppBundle/Utils/script.rb ".$workflow." ".$image;   
        system($command_image); 
        
        $this->createGraph();
    }
    
    public function getWorkflowT2FlowFilepath()
    {
        return $this->getUploadRootDir()."/workflow.t2flow";
    }
    
    public function getWorkflowT2FlowFile()
    {                                 
        return file_get_contents($this->getWorkflowT2FlowFilepath());
    }
    
    public function getWorkflowImage()
    {        
        return file_get_contents($this->getWorkflowImageFilePath());
    }
    
    public function getWorkflowImageFilePath()
    {        
        return $this->getUploadRootDir()."/workflow.svg";;
    }
    
    public function getWorkflowImageFileWebPath()
    {        
        return $this->getWebPath()."/workflow.svg";;
    }
    
    public function getDraftWorkflowImage()
    {
        return file_get_contents($this->getDraftWorkflowImageFilePath());
    }
    
    public function getDraftWorkflowImageFileWebPath()
    {        
        return $this->getWebPath()."/draft-workflow.svg";;
    }
    
    public function getDraftWorkflowImageFilePath()
    {        
        return $this->getUploadRootDir()."/draft-workflow.svg";;
    }
    
    public function getScriptExtension()
    {
        switch ($this->getScriptLanguage())
        {
            case 'python': return 'py';
            case 'bash': return 'sh';
            case 'r': return 'R';
            case 'perl': return 'pl';
            case 'java': return 'java';
        }
    }
    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $workflows;


    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return ScriptConverter
     */
    public function setUpdatedAt($updatedAt = null)
    {
        if (null == $updatedAt)
        {
            $this->updated_at = new \Datetime();
        }
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Add workflows
     *
     * @param \AppBundle\Entity\Workflow $workflows
     * @return ScriptConverter
     */
    public function addWorkflow(\AppBundle\Entity\Workflow $workflows)
    {
        $this->workflows[] = $workflows;
    
        return $this;
    }

    /**
     * Remove workflows
     *
     * @param \AppBundle\Entity\Workflow $workflows
     */
    public function removeWorkflow(\AppBundle\Entity\Workflow $workflows)
    {
        $this->workflows->removeElement($workflows);
    }

    /**
     * Get workflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWorkflows()
    {
        return $this->workflows;
    }
    
    /**
     * @var \AppBundle\Entity\Workflow
     */
    private $workflow;


    /**
     * Set wro
     *
     * @param \AppBundle\Entity\WRO $wro
     * @return ScriptConverter
     */
    public function setWro(\AppBundle\Entity\WRO $wro = null)
    {
        $this->wro = $wro;
    
        return $this;
    }

    /**
     * Get wro
     *
     * @return \AppBundle\Entity\WRO 
     */
    public function getWro()
    {
        return $this->wro;
    }

    /**
     * Set workflow
     *
     * @param \AppBundle\Entity\Workflow $workflow
     * @return ScriptConverter
     */
    public function setWorkflow(\AppBundle\Entity\Workflow $workflow = null)
    {
        $this->workflow = $workflow;
    
        return $this;
    }

    /**
     * Get workflow
     *
     * @return \AppBundle\Entity\Workflow 
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }
    
    public function getWebPath()
    {
        return $this->getUploadDir()."/".$this->getHash();
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir()."/".$this->getHash();
    }

    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents/w2share';
    }
    /**
     * @var string
     */
    private $graph_properties;


    /**
     * Set graph_properties
     *
     * @param string $graphProperties
     * @return ScriptConverter
     */
    public function setGraphProperties($graphProperties)
    { 
        $this->graph_properties = array();
        $graphProperties = str_replace(" ", "", $graphProperties);
        $lines = explode("\n", $graphProperties);
        
        for($i=0; $i<count($lines); $i++)
        {
            if ($lines[$i] == '') break;
            $this->graph_properties[] = explode("=", $lines[$i]);
        }        
        
        return $this;
    }

    /**
     * Get graph_properties
     *
     * @return string 
     */
    public function getGraphProperties()
    {
        if (null === $this->graph_properties)
        {
            return " -c graph.layout=TB -c graph.view=COMBINED ";
        }
        else
        {
            $str = '';
            for($i=0; $i < count($this->graph_properties); $i++)
            {
                $str .= ' -c '.$this->graph_properties[$i][0].'='.strtoupper($this->graph_properties[$i][1]);
            }
        }
        return $str;
    }
}