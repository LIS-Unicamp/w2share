<?php
namespace AppBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\Request;

/**
 * A TWIG Extension which allows to show Controller and Action name in a TWIG view.
 * 
 * The Controller/Action name will be shown in lowercase. For example: 'default' or 'index'
 * 
 */
class ControllerActionExtension extends \Twig_Extension
{
    /**
     * @var Request 
     */
    protected $request;

   /**
    * @var \Twig_Environment
    */
    protected $environment;

    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_controller_name', array($this, 'getControllerName')),
            new \Twig_SimpleFunction('get_action_name', array($this, 'getActionName')),
        );
    }

    /**
    * Get current controller name
    */
    public function getControllerName()
    {
        if(null !== $this->request)
        {
            $pattern = "#Controller\\\([a-zA-Z]*)Controller#";
            $matches = array();
            preg_match($pattern, $this->request->get('_controller'), $matches);

            return strtolower($matches[1]);
        }

    }

    /**
    * Get current action name
    */
    public function getActionName()
    {
        if(null !== $this->request)
        {
            $pattern = "#::([a-zA-Z]*)Action#";
            $matches = array();
            preg_match($pattern, $this->request->get('_controller'), $matches);

            return $matches[1];
        }
    }

    public function getName()
    {
        return 'your_own_controller_action_twig_extension';
    }
}