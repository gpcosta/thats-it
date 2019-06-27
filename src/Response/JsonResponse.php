<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 24/06/2019
 * Time: 17:00
 */

namespace ThatsIt\Response;

/**
 * Class JsonResponse
 * @package ThatsIt\Response
 */
class JsonResponse extends HttpResponse
{
    /**
     * @var array[name => value]
     */
    private $variables;
    
    /**
     * JsonResponse constructor.
     */
    public function __construct()
    {
        $this->setHeader('Content-Type', 'application/json;charset=utf-8');
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
     * @return string
     */
    public function getContent(): string
    {
        return json_encode($this->variables, JSON_PRETTY_PRINT);
    }
}