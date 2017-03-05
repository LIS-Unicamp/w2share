<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
/**
 * YesScript
 */
class ScriptConverter
{
    private $script_file;

    private $script_temp;
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setScriptFile(UploadedFile $file = null)
    {     
        $this->script_file = $file;
        // check if we have an old image path
        if (isset($this->script_path)) {
            // store the old name to delete after the update
            $this->script_temp = $this->script_path;
            $this->script_path = null;
        } else {
            $this->script_path = 'initial';
        }
        $this->preUpload();
        $this->upload();
    }
    
    public function preUpload()
    {
        if (null !== $this->getScriptFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->script_path = $filename.'.'.$this->getScriptFile()->getClientOriginalExtension();
        }               
    }

    public function upload()
    {
        if (null === $this->getScriptFile() 
                && null === $this->getWfdescFile() 
                && null === $this->getProvenanceFile()) {
            return;
        }
        
        if (null !== $this->getScriptFile())
        {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getScriptFile()->move($this->getUploadRootDir(), $this->script_path);

            // check if we have an old image
            if (isset($this->script_temp) && $this->script_temp != '') {
                // delete the old image
                @unlink($this->getUploadRootDir().'/'.$this->script_temp);
                // clear the temp image path
                $this->script_temp = null;
            }
            $this->script_file = null;
        } 
    }

    public function removeUpload()
    {
        $script_file = $this->getScriptAbsolutePath();
        if ($script_file) {
            @unlink($script_file);
        }         
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getScriptFile()
    {
        return $this->script_file;
    }        
    
    public function getScriptAbsolutePath()
    {
        return null === $this->script_path && $this->script_path != ''
            ? null
            : $this->getUploadRootDir().'/'.$this->script_path;
    }
    
    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
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
     * @var \AppBundle\Entity\ResearchObject
     */
    private $ro;

    /**
     * @var \AppBundle\Entity\Person
     */
    private $creator;
    
    public function __construct() 
    {
        $this->hash = sha1(uniqid(mt_rand(), true));
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
     * Set ro
     *
     * @param \AppBundle\Entity\ResearchObject $ro
     * @return ScriptConverter
     */
    public function setRo(\AppBundle\Entity\ResearchObject $ro = null)
    {
        $this->ro = $ro;
    
        return $this;
    }

    /**
     * Get ro
     *
     * @return \AppBundle\Entity\ResearchObject 
     */
    public function getRo()
    {
        return $this->ro;
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
        $fs->dumpFile($this->getUploadRootDir()."/script.".$this->getScriptExtension(), $code);
    }
    
    public function getScriptCode()
    {        
        return file_get_contents($this->getScriptAbsolutePath());
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
    
    public function createGraph()
    {        
        $command = "java -jar ". __DIR__ . "/../../../src/AppBundle/Utils/yesworkflow-0.2.1.1-jar-with-dependencies.jar graph -c extract.comment='#' -c graph.layout=TB -c graph.view=COMBINED -c model.factsfile=" . $this->getUploadRootDir()."/modelfacts.txt " . $this->getScriptAbsolutePath() . " > " . $this->getUploadRootDir() . "/wf.gv; /usr/local/bin/dot -Tpng " . $this->getUploadRootDir() . "/wf.gv -o " . $this->getUploadRootDir()."/workflow.png";                              
        system($command);        
    }
    
    public function downloadWorkflow()
    {        
        $python = $this->getUploadRootDir()."/conversion.py";
        $script = $this->getUploadRootDir()."/script".$this->getScriptExtension();
        $workflow = $this->getUploadRootDir()."/workflow.t2flow";
        $image = $this->getUploadRootDir()."/workflow.svg";
            
        $command_python = "java -jar ".__DIR__."/../../../src/AppBundle/Utils/yesworkflow2taverna.jar ".$script." ".$this->getScriptLanguage()." ".$python;
        system($command_python);
        
        $command_taverna = __DIR__."/../../../vendor/lucasaugustomcc/balcazapy/bin/balc ".$python." ".$workflow;
        system($command_taverna);
        
        $command_image = "ruby ".__DIR__."/../../../src/AppBundle/Utils/script.rb ".$workflow." ".$image;   
        system($command_image);               
        
        return $workflow;
    }
    
    public function getWorkflowImage()
    {
        $image = $this->getUploadRootDir()."/workflow.svg";        
        return file_get_contents($image);
    }
    
    public function getScriptExtension()
    {
        switch ($this->getScriptLanguage())
        {
            case 'python': return 'py';
            case 'bash': return 'sh';
            case 'R': return 'R';
            case 'perl': return 'pl';
            case 'java': return 'java';
        }
    }
}