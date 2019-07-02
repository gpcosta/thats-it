<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 02/07/2019
 * Time: 10:45
 */

namespace ThatsIt\Response;

/**
 * Class RedirectResponse
 * @package ThatsIt\Response
 */
class RedirectResponse extends HttpResponse
{
    /**
     * @var string
     */
    private $url;
    
    /**
     * RedirectResponse constructor.
     * @param string $url
     * @param int $statusCode
     */
    public function __construct(string $url, int $statusCode = 302)
    {
        $this->url = $url;
        if ($statusCode < 300 && $statusCode >= 400) $statusCode = 302;
        $this->statusCode = $statusCode;
    
        $this->setHeader('Location', $this->url);
        $this->setStatusCode($this->statusCode);
    }
    
    /**
     * There is no content in this RedirectResponse
     *
     * @return string
     */
    public function getContent(): string { return ""; }
}