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
     * JsonResponse constructor.
     */
    public function __construct()
    {
        $this->setHeader('Content-Type', 'application/json;charset=utf-8');
    }
    
    /**
     * @return string
     */
    public function getContent(): string
    {
        return json_encode($this->variables, JSON_PRETTY_PRINT);
    }
}