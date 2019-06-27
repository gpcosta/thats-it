<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 15:19
 */

namespace ThatsIt\Response;

/**
 * Class SendResponse
 * @package ThatsIt\Response
 */
class SendResponse
{
    /**
     * @var HttpResponse
     */
    private $response;
    
    /**
     * SendResponse constructor.
     * @param HttpResponse $response
     */
    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
    }
    
    /**
     * send json response
     */
    public function send(): void
    {
        foreach ($this->response->getHeaders() as $header) {
            header($header,false);
        }
        echo $this->response->getContent();
        die;
    }
}
