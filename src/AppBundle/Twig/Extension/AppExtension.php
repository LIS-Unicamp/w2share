<?php

namespace AppBundle\Twig\Extension;

class AppExtension extends \Twig_Extension
{
    public function __construct()
    {
        if (!class_exists('IntlDateFormatter')) {
            throw new RuntimeException('The intl extension is needed to use intl-based filters.');
        }
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('url_decode', array($this, 'urlDecodeFilter')),
        );
    }
    
    public function getFunctions()
    {
        return array(
            'file_exists' => new \Twig_Function_Function('file_exists'),
        );
    }

    public function urlDecodeFilter($string)
    {
        return urldecode($string);
    }
        
    public function getName()
    {
        return 'app_extension';
    }
}

?>
