<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 12:02
 */

namespace ThatsIt\Exception;

use Monolog\Logger;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\HttpResponse;
use Throwable;

/**
 * Class ExceptionWithHTTPResponse - Exception that provides a Response to EntryPoint to be sent
 * @package ThatsIt\Exception
 *
 * @IMPORTANT: this exception should be used when the exception raised should have
 *             attached to it an HTTP response. It's useful for example to cause a
 *             HTTP response that came from an error in Controller constructor
 *             All error pages can be sent by using this exception
 */
class ExceptionWithHTTPResponse extends \Exception
{
	public const LOGGER_LEVEL_NO_LOG = 0;
	public const LOGGER_LEVEL_DEBUG = Logger::DEBUG;
	public const LOGGER_LEVEL_INFO = Logger::INFO;
	public const LOGGER_LEVEL_NOTICE = Logger::NOTICE;
	public const LOGGER_LEVEL_WARNING = Logger::WARNING;
	public const LOGGER_LEVEL_ERROR = Logger::ERROR;
	public const LOGGER_LEVEL_CRITICAL = Logger::CRITICAL;
	public const LOGGER_LEVEL_ALERT = Logger::ALERT;
	public const LOGGER_LEVEL_EMERGENCY = Logger::EMERGENCY;
	
	/**
	 * @var int
	 */
	protected $loggerLevel;
	
	/**
	 * @var mixed|null
	 */
	protected $context;
	
	/**
	 * @var HttpRequest
	 */
	protected $request;
	
	/**
	 * @var HttpResponse
	 */
	protected $response;
	
	/**
	 * Exception constructor.
	 * @param HttpRequest $request - request that cause this exception
	 * @param HttpResponse $response - response to send to the client in case of this exception happens
	 * @param int $code - http code status
	 * @param int $loggerLevel - in which level should be logged such exception
	 * @param string $message - message to log
	 * @see all self::LOGGER_LEVEL_* consts
	 * @param Throwable|null $previous - throwable that had generated this exception
	 * @param mixed|null $context - send anything that is printable to put in log
	 */
	public function __construct(HttpRequest $request, HttpResponse $response, int $code, int $loggerLevel,
								string $message = '', Throwable $previous = null, $context = null)
	{
		parent::__construct($message, $code, $previous);
		
		$this->loggerLevel = $loggerLevel;
		$this->context = $context;
		$this->request = $request;
		$this->response = $response;
	}
	
	/**
	 * @return int
	 */
	public function getLoggerLevel(): int
	{
		return $this->loggerLevel;
	}
	
	/**
	 * @return mixed|null
	 */
	public function getContext()
	{
		return $this->context;
	}
	
	/**
	 * @return HttpResponse
	 */
	public function getResponse(): HttpResponse
	{
		return $this->response;
	}
	
	/**
	 * @return HttpRequest
	 */
	public function getRequest(): HttpRequest
	{
		return $this->request;
	}
}