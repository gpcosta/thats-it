<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 19/08/2022
 * Time: 16:10
 */

namespace ThatsIt\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\HttpResponse;
use ThatsIt\Response\View;

/**
 * Class ErrorController
 * @package ThatsIt\Controller
 */
class ErrorController
{
    /**
     * @var string
     */
    protected $environment;
    
    /**
     * @var HttpRequest
     */
    protected $request;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * ErrorController constructor.
     * @param string $environment
     * @param HttpRequest $request
     * @param LoggerInterface $logger
     */
    public function __construct(string $environment, HttpRequest $request, LoggerInterface $logger)
    {
        $this->environment = $environment;
        $this->request = $request;
        $this->logger = $logger;
    }
    
    /**
     * This method returns an HttpResponse that treats its error/exception
     * If this method throws an exception, the exception will be catched by filp\Whoops library
     *
     * @param $exception
     * @return HttpResponse
     * @throws \Exception
     */
    public function getHttpResponseBasedOnError(Exception $exception): HttpResponse
    {
        if ($exception instanceof ClientException) {
            $this->logger->warning($exception->getMessage(), [
                'code' => $exception->getCode(),
                'exception' => $exception
            ]);
            $response = new View('Error/error');
            $response->addVariable('error', $exception->getMesssage());
            $response->addVariable('statusCode', $exception->getCode());
            $response->setStatusCode($exception->getCode());
            return $response;
        } else {
            if ($exception instanceof PlatformException)
                $this->logger->error($exception->getMessage(), [
                    'code' => $exception->getCode(),
                    'exception' => $exception
                ]);
            else if ($exception instanceof \PDOException)
                $this->logger->error($exception->getMessage(), [
                    'code' => PlatformException::ERROR_DB,
                    'exception' => $exception
                ]);
            else
                $this->logger->emergency($exception->getMessage(), [
                    'code' => PlatformException::ERROR_UNDEFINED,
                    'exception' => $exception
                ]);
        
            if ($this->environment == 'production') {
                $response = new View('Error/error');
                $response->addVariable('error', 'Something wrong happened. Please try again.');
                $response->addVariable('statusCode', 500);
                $response->setStatusCode(500);
                return $response;
            } else {
                throw $exception;
            }
        }
    }
}