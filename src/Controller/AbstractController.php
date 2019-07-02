<?php

namespace ThatsIt\Controller;

use \PDO;
use ThatsIt\Database\Database;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Logger\Logger;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\RedirectResponse;

/**
 * Class AbstractController
 * @package ThatsIt\Controller
 */
abstract class AbstractController
{
    /**
     * @var Database
     */
	private $db;
    
    /**
     * @var HttpRequest
     */
	private $request;
    
    /**
     * @var array
     */
	private $routes;
    
    /**
     * @var Logger
     */
	private $logger;
    
    /**
     * AbstractController constructor.
     * @param HttpRequest $request
     * @param array $routes
     * @param Logger $logger
     * @throws \Exception
     */
    public function __construct(HttpRequest $request, array $routes, Logger $logger)
	{
	    $this->db = new Database();
		$this->request = $request;
		$this->routes = $routes;
		$this->logger = $logger;
	}
    
    /**
     * @return HttpRequest
     */
    protected function getRequest(): HttpRequest
    {
        return $this->request;
    }
    
    /**
     * @return array
     */
    protected function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * @return PDO
     * @throws \ThatsIt\Exception\PlatformException
     */
	protected function getPDO() : PDO
    {
	    return $this->db->getPDO();
    }
    
    /**
     * Redirect to route with that $routeName (after redirect, the process die)
     *
     * @param string $routeName
     * @param array $params
     * @return RedirectResponse
     * @throws PlatformException
     */
    protected function redirectToRoute(string $routeName, array $params = array()): RedirectResponse
    {
        if (isset($this->getRoutes()[$routeName]['path'])) {
            $url = $this->getRoutes()[$routeName]['path'].(count($params) > 0 ? "?".http_build_query($params) : "");
            return new RedirectResponse($url, 302);
        } else {
            throw new PlatformException(
                "There are no possible redirect for ".$routeName.".",
                PlatformException::ERROR_NOT_FOUND_DANGER
            );
        }
    }
}