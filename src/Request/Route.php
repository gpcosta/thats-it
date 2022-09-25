<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 12/11/2020
 * Time: 16:15
 */

namespace ThatsIt\Request;

use ThatsIt\Controller\ErrorController;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Sanitizer\Sanitizer;

/**
 * Class Route
 * @package ThatsIt\Request
 */
class Route
{
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var string
	 */
	private $path;
	
	/**
	 * @var array
	 */
	private $httpMethods;
	
	/**
	 * @var string
	 */
	private $controller;
    
    /**
     * @var null|string
     */
	private $errorController;
	
	/**
	 * @var string
	 */
	private $function;
	
	/**
	 * @var array
	 */
	private $parameters;
	
	/**
	 * @var int
	 */
	private $sanitizer;
	
	/**
	 * @var array
	 */
	private $allRoute;
	
	/**
	 * Route constructor.
	 * @param string $routeName
	 * @param array $routeArray
	 * @throws PlatformException
	 */
	public function __construct(string $routeName, array $routeArray)
	{
		if (!isset($routeArray['path'], $routeArray['httpMethods'], $routeArray['controller'], $routeArray['function'],
				$routeArray['parameters'])) {
			throw new PlatformException('Route '.$routeName.' is incomplete. '.
				'It must have path, httpMethods, controller, function and parameters.', 404);
		}
		
		$this->name = $routeName;
		$this->path = $routeArray['path'];
		$this->httpMethods = $routeArray['httpMethods'];
		$this->controller = $routeArray['controller'];
		$this->errorController = isset($routeArray['errorController']) ? $routeArray['errorController'] :
            ErrorController::class;
		$this->function = $routeArray['function'];
		$this->parameters = $routeArray['parameters'];
		$this->sanitizer = isset($routeArray['sanitizer']) ? $routeArray['sanitizer'] : Sanitizer::SANITIZER_NONE;
		$this->allRoute = $routeArray;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}
	
	/**
	 * @return array
	 */
	public function getHttpMethods(): array
	{
		return $this->httpMethods;
	}
	
	/**
	 * @return string
	 */
	public function getController(): string
	{
		return $this->controller;
	}
    
    /**
     * @return null|string
     */
	public function getErrorController(): ?string
	{
		return $this->errorController;
	}
	
	/**
	 * @return string
	 */
	public function getFunction(): string
	{
		return $this->function;
	}
	
	/**
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}
	
	/**
	 * @param string $name
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getParameter(string $name, $defaultValue = null)
	{
		if (!isset($this->parameters[$name]))
			return $defaultValue;
		return $this->parameters[$name];
	}
	
	/**
	 * @return int
	 */
	public function getSanitizer(): int
	{
		return $this->sanitizer;
	}
	
	/**
	 * @return array
	 */
	public function getAllRoute(): array
	{
		return $this->allRoute;
	}
	
	/**
	 * @param string $fieldName
	 * @param null $defaultValue - in case of this field doens't exist, it will return the default value
	 * @return mixed|null
	 */
	public function getAllRouteField(string $fieldName, $defaultValue = null)
	{
		if (array_key_exists($fieldName, $this->allRoute))
			return $this->allRoute[$fieldName];
		return $defaultValue;
	}
}