<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 24/06/2019
 * Time: 17:00
 */

namespace ThatsIt\Response;

use ThatsIt\Sanitizer\Sanitizer;

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
        $this->setSanitizer(Sanitizer::SANITIZER_UTF8_ENCODE);
    }
    
    /**
     * Echo the body content.
     */
    public function sendContent(): void
    {
        echo json_encode($this->variables, JSON_PRETTY_PRINT);
    }
}