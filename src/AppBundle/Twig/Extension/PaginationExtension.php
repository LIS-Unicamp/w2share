<?php

namespace AppBundle\Twig\Extension;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

class PaginationExtension extends \Twig_Extension
{
    
    /**
     * @var Processor
     */
    protected $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('app_pagination_render', array($this, 'render'), array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }
    
    /**
     * Renders the pagination template
     *
     * @param \Twig_Environment $env
     * @param SlidingPagination $pagination
     * @param string            $template
     * @param array             $queryParams
     * @param array             $viewParams
     *
     * @return string
     */
    public function render(\Twig_Environment $env, SlidingPagination $pagination, $template = null, array $queryParams = array(), array $viewParams = array())
    {
        return $env->render(
            'pagination/pagination.html.twig',
            array ('pagination'=>$pagination)
        );
    }              
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'app_pagination';
    }
}
