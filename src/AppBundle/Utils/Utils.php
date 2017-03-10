<?php

namespace AppBundle\Utils;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Utils 
{    
    private static function friendlyURL($str, $delimiter='-') 
    {
       setlocale(LC_ALL, 'en_US.UTF8');
       $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
       $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
       $clean = strtolower(trim($clean, '-'));
       $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

       return $clean;
    }
   
    public  static function convertNameToUri($module, $name) 
    {
         $str = self::friendlyURL($module).'/'.self::friendlyURL($name);

         return 'http://www.lis.ic.unicamp.br/w2share/'.$str;
    }
    
    public static function findIndexSession($uri, $qualityDimensions) 
    {
        foreach ($qualityDimensions as $index=>$value) 
        {
            if ($value == $uri)
            {
                return $index;
            }
        }
    }
    
    public static function unlinkr($dir, $pattern = "*") 
    {
        // find all files and folders matching pattern
        $files = glob($dir . "/$pattern"); 

        //interate thorugh the files and folders
        foreach($files as $file){ 
        //if it is a directory then re-call unlinkr function to delete files inside this directory     
            if (is_dir($file) and !in_array($file, array('..', '.')))  {
                self::unlinkr($file, $pattern);
                //remove the directory itself
                rmdir($file);
            } else if(is_file($file) and ($file != __FILE__)) {
                // make sure you don't delete the current script
                unlink($file); 
            }
        }
    } 

}