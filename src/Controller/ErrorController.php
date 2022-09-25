<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 19/08/2022
 * Time: 16:10
 */

namespace ThatsIt\Controller;

use Monolog\Logger;
use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Response\HttpResponse;
use ThatsIt\Response\View;

/**
 * Class ErrorController
 * @package ThatsIt\Controller
 */
class ErrorController
{
    /**
     * @var \Exception
     */
    protected $exception;
    
    /**
     * @var Logger 
     */
    protected $logger;
    
    /**
     * @var string 
     */
    protected $environment;
    
    /**
     * ErrorController constructor.
     * @param \Exception $e
     * @param Logger $logger
     * @param string $environment
     */
    public function __construct(\Exception $e, Logger $logger, string $environment)
    {
        $this->exception = $e;
        $this->logger = $logger;
        $this->environment = $environment;
    }
    
    /**
     * This method returns an HttpResponse that treats its error/exception
     * If this method throws an exception, the exception will be catched by filp\Whoops library
     * 
     * @return HttpResponse
     * @throws \Exception
     */
    public function getHttpResponseBasedOnError(): HttpResponse
    {
        if ($this->exception instanceof ClientException) {
            $this->logger->addWarning($this->exception->getMessage(), [
                'code' => $this->exception->getCode(),
                'exception' => $this->exception
            ]);
            $response = new View('Error/error');
            $response->addVariable('error', $this->exception->getMesssage());
            $response->addVariable('statusCode', $this->exception->getCode());
            $response->setStatusCode($this->exception->getCode());
            return $response;
        } else {
            if ($this->exception instanceof PlatformException)
                $this->logger->addError($this->exception->getMessage(), [
                    'code' => $this->exception->getCode(),
                    'exception' => $this->exception
                ]);
            else if ($this->exception instanceof \PDOException)
                $this->logger->addError($this->exception->getMessage(), [
                    'code' => PlatformException::ERROR_DB,
                    'exception' => $this->exception
                ]);
            else
                $this->logger->addEmergency($this->exception->getMessage(), [
                    'code' => PlatformException::ERROR_UNDEFINED,
                    'exception' => $this->exception
                ]);
        
            if ($this->environment == 'production') {
                $response = new View('Error/error');
                $response->addVariable('error', 'Something wrong happened. Please try again.');
                $response->addVariable('statusCode', 500);
                $response->setStatusCode(500);
                return $response;
            } else {
                throw $this->exception;
            }
        }
    }
}