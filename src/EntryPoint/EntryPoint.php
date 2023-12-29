<?php

namespace ThatsIt\EntryPoint;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ThatsIt\Controller\ValidateController;
use ThatsIt\Exception\ExceptionWithHTTPResponse;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\JsonResponse;
use ThatsIt\Response\View;

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
	 * @var LoggerInterface
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
			
			//$this->logger->info('Request: ' . $this->request->getRawBody());
		} catch (\Exception $e) {
			if ($this->environment == 'production') {
				$this->sendJsonErrorMessage(
					500,
                    [
                        'status' => [
                            'code' => 500,
                            'message' => 'The service that you are trying to contact cannot fulfill your request at the moment.'
                        ]
                    ]
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
			$response->send();
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
			$e->getResponse()->send();
		} catch (\ErrorException | \Exception $e) {
            $validateController->getErrorResponse($this->environment, $this->request, $this->logger, $e)->send();
		}
	}
    
    /**
     * @param int $statusCode
     * @param array $message
     */
    public static function sendJsonErrorMessage(int $statusCode, array $message): void
    {
        $response = new JsonResponse();
        $response->setStatusCode($statusCode);
        $response->setVariables($message);
        $response->send();
    }
    
    /**
     * @param string $page
     * @param int $statusCode
     * @param string $error
     */
    public static function sendViewErrorMessage(string $page, int $statusCode, string $error): void
    {
        $response = new View($page);
        $response->setStatusCode($statusCode);
        $response->addVariable('error', $error);
        $response->send();
    }
}