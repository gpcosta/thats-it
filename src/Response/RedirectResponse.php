<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 02/07/2019
 * Time: 10:45
 */

namespace ThatsIt\Response;


class RedirectResponse extends HttpResponse
{
    private $statusCode;
    
    public function __construct(int $statusCode = 302)
    {
        if ($statusCode < 300 && $statusCode >= 400) $statusCode = 302;
        $this->statusCode = $statusCode;
    }
    
    public function getContent(): string { return ""; }
    
    /**
     * Sets the headers for a redirect.
     *
     * @param string $url
     * @param int $statusCode
     */
    public function redirect(string $url): void
    {
        $this->setHeader('Location', $url);
        $this->setStatusCode($this->statusCode);
    }
}