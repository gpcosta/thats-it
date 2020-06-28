<?php

namespace ThatsIt\EntryPoint;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use ThatsIt\Controller\ValidateController;
use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\PlatformException;
use ThatsIt\FunctionsBag\FunctionsBag;
use ThatsIt\Logger\Logger;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\HttpResponse;
use ThatsIt\Response\SendResponse;
use ThatsIt\Response\View;
use ThatsIt\Sanitizer\ArrayOfInputsToSanitize;
use ThatsIt\Sanitizer\InputToSanitize;
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
     * @param array $routes
     * @param array $generalConfig
     * @param string $environment
     */
	public function __construct(HttpRequest $request, array $routes, array $generalConfig, string $environment)
	{
	    $this->request = $request;
	    $this->routes = $routes;
        $this->generalConfig = $generalConfig;
        $this->environment = $environment;
        
        FunctionsBag::setMyDomain($this->generalConfig['domain']);
        
        try {
            $this->logger = new Logger('ThatsIt');
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
            FunctionsBag::setHttpHost($this->request->getHost());
            $info = $this->getControllerAndFunction();
            $currentRoute = $this->findRoute($info['controller'], $info['function']);
            if ($currentRoute === null) {
                throw new PlatformException(
                    "There is no such route.",
                    PlatformException::ERROR_NOT_FOUND_DANGER
                );
            }
            $validateController = new ValidateController($currentRoute);
            $givenParameters = array_merge($this->request->getParameters(), $info['vars']);
            $parameters = $validateController->getCorrectParameters($givenParameters);
            $parameters = $this->getSanitizedParameters($parameters, $currentRoute['parameters']);
            $controllerToCall = new $info['controller']($this->environment, $this->request,
                $this->routes, $currentRoute, $this->logger);
            $response = call_user_func_array(array($controllerToCall, $info['function']), $parameters);
    
            if ($response instanceof HttpResponse) {
                $send = new SendResponse($response);
                $send->send();
            } else {
                throw new PlatformException(
                    "Controller has to return a class that implements ThatsIt\\Response\\HttpResponse class.",
                    PlatformException::ERROR_RESPONSE
                );
            }
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
    
    /**
     * @return Dispatcher
     */
    private function getDispatcher(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $routeName => $route) {
                $handler = array(
                    'controller' => $route['controller'],
                    'function' => $route['function']
                );
                $r->addRoute($route['httpMethods'], $route['path'], $handler);
            }
        });
    }
    
    /**
     * @return array['routeName', 'controller', 'function', 'vars' => [...]]
     * @throws \Exception
     */
    private function getControllerAndFunction(): array
    {
        $toReturn = ['controller' => null, 'function' => null, 'vars' => array()];
        $routeInfo = $this->getDispatcher()->dispatch($this->request->getMethod(), $this->request->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new ClientException("Not found what was requested.", 404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                throw new ClientException("The http method used is not valid. The only valid ones are ".
                    implode(", ", $allowedMethods).".", 405);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                
                $vars = $routeInfo[2];
                $toReturn['controller'] = $handler['controller'];
                $toReturn['function'] = $handler['function'];
                $toReturn['vars'] = $vars;
                
                break;
        }
        
        return $toReturn;
    }
    
    /**
     * Sanitize $givenParameters with the provided sanitizer
     * methods provided by $currentRouteParameters
     *
     * @param array $givenParameters
     * @param array $currentRouteParameters
     * @return array
     */
    private function getSanitizedParameters(array $givenParameters, array $currentRouteParameters): array
    {
        foreach ($givenParameters as $parameterName => $parameterValue) {
            $sanitizerToUse = (isset($currentRouteParameters[$parameterName]['sanitizer']) ?
                $currentRouteParameters[$parameterName]['sanitizer'] : Sanitizer::SANITIZER_TEXT_ONLY
            );
            if (!is_array($parameterValue) && !is_object($parameterValue)) {
                $givenParameters[$parameterName] = (new InputToSanitize(
                    $parameterName, $parameterValue, $sanitizerToUse
                ))->getSanitizedInput();
            } else if (is_array($parameterValue)) {
                $givenParameters[$parameterName] = (new ArrayOfInputsToSanitize(
                    $parameterName, $parameterValue, $sanitizerToUse
                ))->getSanitizedInput();
            }
        }
        return $givenParameters;
    }
    
    /**
     * @param string $controllerName
     * @param string $functionName
     * @return array|null
     */
    private function findRoute(string $controllerName, string $functionName): ?array
    {
        foreach ($this->routes as $routeName => $route) {
            if ($route['controller'] == $controllerName && $route['function'] == $functionName) {
                $route['routeName'] = $routeName;
                return $route;
            }
        }
        return null;
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