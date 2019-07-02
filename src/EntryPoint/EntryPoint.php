<?php

namespace ThatsIt\EntryPoint;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use ThatsIt\Controller\ValidateController;
use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Logger\Logger;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\HttpResponse;
use ThatsIt\Response\SendResponse;

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
     * @var Logger
     */
	private $logger;
    
    /**
     * EntryPoint constructor.
     * @param HttpRequest $request
     * @param array $routes
     * @param Logger $logger
     */
	public function __construct(HttpRequest $request, array $routes, Logger $logger)
	{
	    $this->request = $request;
	    $this->routes = $routes;
	    $this->logger = $logger;
	}
    
    /**
     * @throws ClientException
     * @throws PlatformException
     * @throws \Exception
     */
	public function callController()
	{
        $info = $this->getControllerAndFunction();
        $validateController = new ValidateController($this->findRoute($info['controller'], $info['function']));
        $parameters = $validateController->getCorrectParameters(array_merge($this->request->getParameters(), $info['vars']));
        
        try {
            $controllerToCall = new $info['controller']($this->request, $this->routes, $this->logger);
        } catch (ClientException $e) {
            throw $e;
        } catch (PlatformException $e) {
            throw $e;
        } catch (\PDOException $e) {
            throw new PlatformException("There was a problem connecting to DB. ".
                "Please verify if config/database.php has the correct info.", PlatformException::ERROR_DB, $e);
        } catch (\Exception $e) {
            throw new PlatformException("The requested controller (".$info['controller'].") was not found.",
                PlatformException::ERROR_NOT_FOUND_DANGER, $e);
        }
        
        try {
            $response = call_user_func_array(array($controllerToCall, $info['function']), $parameters);
        } catch (ClientException $e) {
            throw $e;
        } catch (PlatformException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PlatformException("The function requested (".$info['function'].") was not found ".
                "or has not defined the correct parameters.", PlatformException::ERROR_NOT_FOUND_DANGER, $e);
        }
        
        if ($response instanceof HttpResponse) {
            $send = new SendResponse($response);
            $send->send();
        } else {
            throw new PlatformException(
                "Controller has to return a class that implements ThatsIt\\Response\\HttpResponse class.",
                PlatformException::ERROR_RESPONSE
            );
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
     * @param string $controllerName
     * @param string $functionName
     * @return array|null
     */
    private function findRoute(string $controllerName, string $functionName): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['controller'] == $controllerName && $route['function'] == $functionName)
                return $route;
        }
        return null;
    }
}