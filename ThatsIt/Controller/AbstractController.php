<?php

namespace ThatsIt\Controller;

use \PDO;
use ThatsIt\Database\Database;
use ThatsIt\Logger\Logger;
use ThatsIt\Request\HttpRequest;

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
     */
	protected function getPDO() : PDO
    {
	    return $this->db->getPDO();
    }
}