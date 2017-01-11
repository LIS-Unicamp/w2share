<?php

namespace Neo\AdminBundle\Twig\Extension;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;
    
    /**
     * {@inheritDoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'neo_pagination_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
            'neo_sortable_render' => new \Twig_Function_Method($this, 'sort', array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders the pagination template
     *
     * @param string $template
     * @param array $queryParams
     * @param array $viewParams
     *
     * @return string
     */
    public function render($pagination)
    {
        return $this->environment->render(
            'NeoAdminBundle:Pagination:pagination.html.twig',
            array ('pagination'=>$pagination)
        );
    }
    
    /**
     * Renders the pagination template
     *
     * @param string $template
     * @param array $queryParams
     * @param array $viewParams
     *
     * @return string
     */
    public function sort($nome, $coluna, $pagination, $style='', $classe='')
    {
        return $this->environment->render(
            'NeoAdminBundle:Pagination:sortable.html.twig',
                array('nome' => $nome, 
                    'coluna' => $coluna, 
                    'pagination' => $pagination,
                    'style' => $style,
                    'classe' => $classe)
        );
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'neo_pagination';
    }
}
