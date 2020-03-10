<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 20:18
 */

namespace ThatsIt\Response;

use ThatsIt\Folder\Folder;

/**
 * Class View
 * @package ThatsIt\Response
 */
class View extends HttpResponse
{
    /**
     * @var string|null
     */
    private $pageToShow;
    
    /**
     * @var Component|null
     */
    private $component;
    
    /**
     * @var array
     */
    private $routes;
    
    /**
     * View constructor.
     * @param $viewOrComponent - if it's a string, it is interpreted as the name of a view
     *                           else if it's a Component, it is used as Component
     */
    public function __construct($viewOrComponent)
    {
        if ($viewOrComponent instanceof Component) {
            $this->component = $viewOrComponent;
            $this->pageToShow = null;
        } else if (is_string($viewOrComponent)) {
            $this->pageToShow = $viewOrComponent;
            $this->component = null;
        }
    }
    
    /**
     * @return string
     */
    public function getContent(): string
    {
        ob_start();
        extract($this->variables);
        // if there is a page to show, it will show it
        if ($this->pageToShow)
            require_once(Folder::getSourceFolder().'/View/'.$this->pageToShow.'.php');
        // if there is a component, will print it
        else if ($this->component)
            print_r($this->component);
        return ob_get_clean();
    }
}