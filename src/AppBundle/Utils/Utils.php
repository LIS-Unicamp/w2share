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
    
    public static function unlinkr($src) 
    {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    self::unlinkr($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    } 

}