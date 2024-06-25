<?php

namespace ThatsIt\Controller;

use Psr\Log\LoggerInterface;
use ThatsIt\Configurations\Configurations;
use ThatsIt\Database\Database;
use ThatsIt\Database\PDO;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\HttpResponse;
use ThatsIt\Security\CSRFToken;

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
	 * @var string
	 */
	private $environment;
	
	/**
	 * @var HttpRequest
	 */
	private $request;
	
	/**
	 * @var LoggerInterface
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
     * @param LoggerInterface $logger
     * @throws PlatformException
     */
	public function __construct(string $environment, HttpRequest $request, LoggerInterface $logger)
	{
		$this->db = new Database();
		if (in_array($environment, [Configurations::ENVIRONMENT_DEV, Configurations::ENVIRONMENT_PROD])) {
			$this->environment = $environment;
		} else {
			$this->environment = Configurations::ENVIRONMENT_PROD;
		}
		$this->request = $request;
		$this->logger = $logger;
		
		$this->csrfToken = CSRFToken::getCSRFTokenFromCookies($request);
	}
    
    /**
     * @param HttpResponse $response
     * @return HttpResponse
     */
	function interceptResponse(HttpResponse $response): HttpResponse
    {
        return $response;
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
	 * @return LoggerInterface
	 */
	protected function getLogger(): LoggerInterface
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
	protected function getPDO(): PDO
	{
		return $this->db->getPDO();
	}
}