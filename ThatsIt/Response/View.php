<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 20:18
 */

namespace ThatsIt\Response;

use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\PlatformException;

/**
 * Class View
 * @package ThatsIt\View
 */
class View extends HttpResponse
{
    
    /**
     * @var string
     */
    private $viewToShow;
    
    /**
     * @var array[name => value]
     */
    private $variables;
    
    /**
     * @var array
     */
    private $routes;
    
    /**
     * View constructor.
     * @param string $viewToShow
     */
    public function __construct(string $viewToShow)
    {
        $this->viewToShow = $viewToShow;
        $this->variables = array();
    }
    
    /**
     * @param string $name
     * @param $value
     */
    public function addVariable(string $name, $value): void
    {
        $this->variables[$name] = $value;
    }
    
    /**
     * @param array $variables
     */
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
    
    /**
     * @param string $name
     * @param bool $withOptional (url with optional part or not. when there is no optional part, doesn't matter its value)
     * @param array $variables[name => value]
     * @return string
     * @throws PlatformException
     */
    public function getUrl(string $name, bool $withOptional = false, array $variables = array()): string
    {
        // just to load routes once
        if (!$this->routes) $this->routes = Configurations::getRoutesConfig();
        
        if (!isset($this->routes[$name])) {
            throw new PlatformException("There are no url for '".$name."'",
                PlatformException::ERROR_NOT_FOUND_DANGER);
        }
        
        $path = $this->routes[$name]['path'];
        // will substitute all variables for their value
        foreach ($variables as $name => $value) {
            $path = preg_replace("/\{".$name."(\:.*){0,1}\}/U", $value, $path);
        }
        
        if ($withOptional) {
            // if so removes just parenthesis
            $path = preg_replace("/\(|\)/", "", $path);
        } else {
            // else removes everything that is inside of parenthesis
            $path = preg_replace("/\(.*\)/", "", $path);
        }
        
        // if there are some more variables to substitute, it will raise a exception
        preg_match("/\{.*\}/", $path, $matches);
        if (isset($matches[0])) {
            throw new PlatformException("There are some variables that weren't replaced.",
                PlatformException::ERROR_NOT_FOUND_DANGER);
        }
        
        return $path;
    }
    
    /**
     * @return string
     */
    public function getContent(): string
    {
        ob_start();
        extract($this->variables);
        require_once(__DIR__.'/../../src/View/'.$this->viewToShow.'.php');
        return ob_get_clean();
    }
}