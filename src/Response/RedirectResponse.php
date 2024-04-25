<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 02/07/2019
 * Time: 10:45
 */

namespace ThatsIt\Response;

use ThatsIt\Sanitizer\Sanitizer;

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
        if ($statusCode < 300 && $statusCode >= 400)
            $statusCode = 302;
        
        $this->setHeader('Location', $this->url);
        $this->setStatusCode($statusCode);
    }
    
    /**
     * Add a variable to url (will allow to change the url when variables are added)
     *
     * @param string $name
     * @param $value
     * @param int|null $sanitizer
     * @return void
     */
    public function addVariable(string $name, $value, ?int $sanitizer = null): void
    {
        parent::addVariable($name, $sanitizer === null ? $value : Sanitizer::sanitize($value, $sanitizer));
        $this->setHeader('Location', $this->getNewUrl());
    }
    
    /**
     * Set variables to url
     *
     * @param array $variables
     */
    public function setVariables(array $variables): void
    {
        parent::setVariables($variables);
        $this->setHeader('Location', $this->getNewUrl());
    }
    
    /**
     * There is no content in this RedirectResponse
     */
    public function sendContent(): void
    {
        return;
    }
    
    /**
     * Return the url formed with the addition of the current variables
     *
     * @return string
     */
    private function getNewUrl(): string
    {
		$params = $this->variables;
		parse_str(parse_url($this->url, PHP_URL_QUERY), $queryParams);
		foreach ($queryParams as $paramName => $paramValue) {
			if (!array_key_exists($paramName, $this->variables))
				$params[$paramName] = $paramValue;
		}
	
		return strtok($this->url, '?').'?'.http_build_query($params);
    }
}