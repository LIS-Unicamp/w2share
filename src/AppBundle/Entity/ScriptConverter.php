<?php

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents/yesscript';
    }
}

