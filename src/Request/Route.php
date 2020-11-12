<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 12/11/2020
 * Time: 16:15
 */

namespace ThatsIt\Request;

use ThatsIt\Exception\PlatformException;

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
	 * @var string
	 */
	private $function;
	
	/**
	 * @var array
	 */
	private $parameters;
	
	/**
	 * Route constructor.
	 * @param string $routeName
	 * @param array $routeArray
	 * @throws PlatformException
	 */
	public function __construct(string $routeName, array $routeArray)
	{
		if (isset($routeArray['path'], $routeArray['httpMethods'], $routeArray['controller'], $routeArray['function'],
				$route['parameters'])) {
			throw new PlatformException('Route '.$routeName.' is incomplete. '.
				'It must have path, httpMethods, controller, function and parameters.');
		}
		
		$this->name = $routeName;
		$this->path = $routeArray['path'];
		$this->httpMethods = $routeArray['httpMethods'];
		$this->controller = $routeArray['controller'];
		$this->function = $routeArray['function'];
		$this->parameters = $routeArray['parameters'];
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
}