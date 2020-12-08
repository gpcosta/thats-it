<?php

namespace ThatsIt\EntryPoint;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
					'Error/error500'
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
		try {
			$validateController = new ValidateController($this->request);
			$validateController->callConstructor($this->environment, $this->request, $this->logger);
			$response = $validateController->callMethod();
			$response->setEnvironment($this->environment);
			$response->setCurrentUrl($this->request->getPath());
			
			$send = new SendResponse($response);
			$send->send();
		} catch (ExceptionWithHTTPResponse $e) {
			if ($e->getLoggerLevel() !== ExceptionWithHTTPResponse::LOGGER_LEVEL_NO_LOG) {
				$this->logger->addRecord($e->getLoggerLevel(), $e->getMessage(), [
					'uri' => $e->getRequest()->getUri(),
					'parameters' => $e->getRequest()->getParameters(),
					'cookies' => $e->getRequest()->getCookies(),
					'code' => $e->getCode(),
					'trace' => $e->getTraceAsString(),
					'context' => $e->getContext()
				]);
			}
			$sendError = new SendResponse($e->getResponse());
			$sendError->send();
		} catch (ClientException $e) {
			$this->logger->addWarning($e->getMessage(), ["code" => $e->getCode(), "exception" => $e]);
			if ($e->getCode() == 404) $this->sendErrorMessage(404, $e->getMessage(), 'Error/error404');
			else if ($e->getCode() == 405) $this->sendErrorMessage(405, $e->getMessage(), 'Error/error405');
			else $this->sendErrorMessage(500, $e->getMessage(), 'Error/error500');
		} catch (PlatformException | \PDOException | \Exception $e) {
			if ($e instanceof PlatformException)
				$this->logger->addError($e->getMessage(), ["code" => $e->getCode(), "exception" => $e]);
			else if ($e instanceof \PDOException)
				$this->logger->addError($e->getMessage(), ["code" => PlatformException::ERROR_DB, "exception" => $e]);
			else
				$this->logger->addEmergency($e->getMessage(), ["code" => PlatformException::ERROR_UNDEFINED, "exception" => $e]);
			
			if ($this->environment == 'production') {
				$this->sendErrorMessage(
					500,
					"Something wrong happened. Please try again.",
					'Error/error500'
				);
			} else {
				throw $e;
			}
		}
	}
	
	private function sendErrorMessage(int $statusCode, string $error, string $page): void
	{
		$response = new View($page);
		$response->setStatusCode($statusCode);
		$response->addVariable('error', $error);
		$send = new SendResponse($response);
		$send->send();
	}
}