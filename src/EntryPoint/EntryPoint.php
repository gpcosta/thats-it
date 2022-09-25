<?php

namespace ThatsIt\EntryPoint;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ThatsIt\Controller\ErrorController;
use ThatsIt\Controller\ValidateController;
use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\ExceptionWithHTTPResponse;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;
use ThatsIt\FunctionsBag\FunctionsBag;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\SendResponse;
use ThatsIt\Response\View;
use ThatsIt\Sanitizer\Sanitizer;

/**
 * Class EntryPoint
 * @package ThatsIt\EntryPoint
 */
class EntryPoint
{
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
	private $generalConfig;
	
	/**
	 * @var string
	 */
	private $environment;
	
	/**
	 * @var Logger
	 */
	private $logger;
	
	/**
	 * EntryPoint constructor.
	 * @param HttpRequest $request
	 * @param string $environment
	 */
	public function __construct(HttpRequest $request, string $environment)
	{
		$this->request = $request;
		$this->environment = $environment;
		
		try {
			$this->logger = new Logger('ThatsIt');
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/debug.log", Logger::DEBUG, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/info.log", Logger::INFO, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/notice.log", Logger::NOTICE, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/warning.log", Logger::WARNING, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/error.log", Logger::ERROR, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/critical.log", Logger::CRITICAL, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/alert.log", Logger::ALERT, false));
			$this->logger->pushHandler(new StreamHandler(Folder::getLogFolder() . "/emergency.log", Logger::EMERGENCY, false));
		} catch (\Exception $e) {
			if ($this->environment == 'production') {
				$this->sendErrorMessage(
					500,
					"The service that you are trying to contact cannot fulfill your request at the moment.",
					'Error/error'
				);
			}
		}
	}
	
	/**
	 * @throws PlatformException
	 * @throws \PDOException
	 * @throws \Exception
	 */
	public function callController()
	{
        $validateController = new ValidateController($this->request);
        try {
			$validateController->callConstructor($this->environment, $this->request, $this->logger);
			$response = $validateController->callMethod();
			$response->setEnvironment($this->environment);
			$response->setCurrentFullUrl($this->request->getHost(true).$this->request->getUri());
			$response->setCurrentUrlPath($this->request->getHost(true).$this->request->getPath());
			
			$send = new SendResponse($response);
			$send->send();
		} catch (ExceptionWithHTTPResponse $e) {
			if ($e->getLoggerLevel() !== ExceptionWithHTTPResponse::LOGGER_LEVEL_NO_LOG) {
				$this->logger->addRecord($e->getLoggerLevel(), $e->getMessage(), [
					'uri' => $e->getRequest()->getUri(),
					'parameters' => $e->getRequest()->getAllParameters(),
					'cookies' => $e->getRequest()->getCookies(),
					'code' => $e->getCode(),
					'trace' => $e->getTraceAsString(),
					'context' => $e->getContext()
				]);
			}
			$sendError = new SendResponse($e->getResponse());
			$sendError->send();
		} catch (\ErrorException | \Exception $e) {
            $sendError = new SendResponse($validateController->getErrorResponse($e, $this->logger, $this->environment));
            $sendError->send();
		}
	}
}