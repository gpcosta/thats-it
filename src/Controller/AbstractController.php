<?php

namespace ThatsIt\Controller;

use Monolog\Logger;
use ThatsIt\Configurations\Configurations;
use ThatsIt\Database\Database;
use ThatsIt\Database\PDO;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Request\HttpRequest;
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
	 * @param Logger $logger
	 * @throws PlatformException
	 */
	public function __construct(string $environment, HttpRequest $request, Logger $logger)
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
	protected function getPDO(): PDO
	{
		return $this->db->getPDO();
	}
}