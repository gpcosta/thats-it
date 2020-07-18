<?php

namespace ThatsIt\Controller;

use \PDO;
use ThatsIt\Database\Database;
use ThatsIt\Exception\PlatformException;
use ThatsIt\FunctionsBag\FunctionsBag;
use ThatsIt\Logger\Logger;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\RedirectResponse;
use ThatsIt\Session\CSRFToken;

/**
 * Class AbstractController
 * @package ThatsIt\Controller
 */
abstract class AbstractController
{
    const ENVIRONMENT_DEV = "development";
    const ENVIRONMENT_PROD = "production";
    
    /**
     * @var Database
     */
	private $db;
    
    /**
     * @var string
     */
	private $environment;
    
    /**
     * @var HttpRequest
     */
	private $request;
    
    /**
     * @var array
     */
	private $routes;
    
    /**
     * @var array
     */
    private $currentRoute;
    
    /**
     * @var Logger
     */
	private $logger;
    
    /**
     * @var null|CSRFToken
     */
    private $csrfToken;
    
    /**
     * AbstractController constructor.
     * @param string $environment
     * @param HttpRequest $request
     * @param array $routes
     * @param array $currentRoute
     * @param Logger $logger
     * @throws PlatformException
     */
    public function __construct(string $environment, HttpRequest $request, array $routes, array $currentRoute, Logger $logger)
	{
	    $this->db = new Database();
        if (in_array($environment, [self::ENVIRONMENT_DEV, self::ENVIRONMENT_PROD])) {
            $this->environment = $environment;
        } else {
            $this->environment = self::ENVIRONMENT_PROD;
        }
		$this->request = $request;
		$this->routes = $routes;
        $this->currentRoute = $currentRoute;
		$this->logger = $logger;
        
        $this->csrfToken = CSRFToken::getCSRFTokenFromCookies($request);
	}
    
    /**
     * @return string
     */
    protected function getEnvironment(): string
    {
        return $this->environment;
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
     * @return array
     */
    protected function getCurrentRoute(): array
    {
        return $this->currentRoute;
    }
    
    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * @return null|CSRFToken
     */
    protected function getCsrfToken(): ?CSRFToken
    {
        return $this->csrfToken;
    }
    
    /**
     * @return PDO
     * @throws \ThatsIt\Exception\PlatformException
     */
	protected function getPDO() : PDO
    {
	    return $this->db->getPDO();
    }
}