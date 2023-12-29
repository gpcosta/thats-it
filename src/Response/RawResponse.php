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
class RawResponse extends HttpResponse
{
	private $content = '';
	
    /**
     * JsonResponse constructor.
     */
    public function __construct()
    {
        $this->setHeader('Content-Type', 'text/plain;charset=utf-8');
    }
    
    /**
     * Echo the body content.
     */
    public function sendContent(): void
    {
        echo $this->content;
    }
	
	/**
	 * @param string $content
	 */
    public function setContent(string $content): void
	{
		$this->content = $content;
	}
}