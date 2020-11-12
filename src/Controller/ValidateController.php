<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/06/2019
 * Time: 18:21
 */

namespace ThatsIt\Controller;

use Monolog\Logger;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\HttpResponse;

/**
 * Class ValidateController
 * @package ThatsIt\Controller
 */
class ValidateController
{
	/**
	 * this constant will hold how many default arguments a controller constructor takes
	 * this means that constructor takes at least this number of arguments
	 * and can take some more, for example variables passed as parameters from a request
	 */
	private const ARGS_DEFAULT_CONSTRUCTOR = 3;
	
	/**
	 * @var array
	 */
	private $route;
	
	/**
	 * @var string
	 */
	private $controllerName;
	
	/**
	 * @var \ReflectionClass
	 */
	private $reflectionController;
	
	/**
	 * @var AbstractController|null
	 */
	private $controller;
	
	/**
	 * @var string
	 */
	private $methodName;
	
	/**
	 * @var \ReflectionMethod
	 */
	private $reflectionMethod;
	
	/**
	 * @var array
	 */
	private $routeParameters;
	
	/**
	 * @var array
	 */
	private $givenParameters;
	
	/**
	 * @var array
	 */
	private $correctParameters;
	
	/**
	 * ValidateController constructor.
	 * @param string $controllerName
	 * @param string $methodName
	 * @param array $routeParameters
	 * @param array $givenParameters
	 * @throws PlatformException
	 * @throws \ReflectionException
	 */
	public function __construct(HttpRequest $request)
	{
		$this->controllerName = $request->getCurrentRoute()->getController();
		$this->reflectionController = new \ReflectionClass($request->getCurrentRoute()->getController());
		$this->controller = null;
		$this->methodName = $request->getCurrentRoute()->getFunction();
		$this->reflectionMethod = $this->reflectionController->getMethod($this->methodName);
		$this->routeParameters = $request->getCurrentRoute()->getParameters();
		$this->givenParameters = $request->getParameters();
		$this->correctParameters = $this->getCorrectParameters();
	}
	
	/**
	 * @param string $environment
	 * @param HttpRequest $request
	 * @param Logger $logger
	 * @return ValidateController
	 */
	public function callConstructor(string $environment, HttpRequest $request, Logger $logger): self
	{
		$constructorParameters = $this->reflectionController->getConstructor()->getParameters();
		$args = [$environment, $request, $logger];
		for ($i = self::ARGS_DEFAULT_CONSTRUCTOR, $len = count($constructorParameters); $i < $len; $i++) {
			$parameter = $constructorParameters[$i];
			$args[] = $this->correctParameters[$parameter->getName()];
		}
		$this->controller = $this->reflectionController->newInstanceArgs($args);
		return $this;
	}
	
	/**
	 * @return HttpResponse
	 * @throws PlatformException
	 */
	public function callMethod(): HttpResponse
	{
		$methodParameters = $this->reflectionMethod->getParameters();
		$args = [];
		foreach ($methodParameters as $key => $parameter) {
			if (!array_key_exists($parameter->getName(), $this->correctParameters))
				throw new PlatformException(
					'Controller method has parameters that were not passed.',
					400
				);
			
			$args[] = $this->correctParameters[$parameter->getName()];
		}
		
		if (!$this->controller)
			throw new PlatformException(
				'There was an error calling Controller constructor.',
				500
			);
		
		$response = $this->reflectionMethod->invokeArgs($this->controller, $args);
		if (!($response instanceof HttpResponse))
			throw new PlatformException(
				"Controller method has to return a class that implements ThatsIt\\Response\\HttpResponse class.",
				PlatformException::ERROR_RESPONSE
			);
		
		return $response;
	}
	
	/**
	 * @return array
	 * @throws PlatformException
	 */
	private function getCorrectParameters(): array
	{
		$controllerReflectionParametersByName = $this->getControllerReflectionParametersByName();
		$correctParameters = [];
		foreach ($this->routeParameters as $name => $infoAboutParameter) {
			// verify if route parameter exists in controller contructor or method parameters
			if (!array_key_exists($name, $controllerReflectionParametersByName))
				throw new PlatformException(
					"Controller parameters are incompatible with Route parameters.",
					PlatformException::ERROR_NOT_FOUND_DANGER
				);
			
			if (!array_key_exists($name, $this->givenParameters)) {
				// there is no given parameter and no default value
				if (!array_key_exists('default', $infoAboutParameter))
					throw new PlatformException(
						"Given parameters are incompatible with Route parameters.",
						PlatformException::ERROR_NOT_FOUND_DANGER
					);
				
				// there is no given parameter but exists default value
				$correctParameters[$name] = $infoAboutParameter['default'];
			} else {
				// there is given parameter for this name
				$correctParameters[$name] = $this->givenParameters[$name];
			}
			
			$expectedType = $controllerReflectionParametersByName[$name]->getType();
			// verify type of parameter
			if ($expectedType !== null && !$this->verifyType($correctParameters[$name], $expectedType->getName()))
				throw new PlatformException(
					"'" . $name . "' parameter should be from " . $expectedType->getName() . " type.",
					PlatformException::ERROR_NOT_FOUND_DANGER
				);
		}
		return $correctParameters;
	}
	
	/**
	 * Get reflection parameters indexed by name of both constructor and method from controller
	 *
	 * @return array
	 */
	private function getControllerReflectionParametersByName(): array
	{
		$controllerReflectionParameters = array_merge(
			array_slice(
				$this->reflectionController->getConstructor()->getParameters(),
				self::ARGS_DEFAULT_CONSTRUCTOR
			),
			$this->reflectionMethod->getParameters()
		);
		
		$controllerReflectionParametersByName = [];
		foreach ($controllerReflectionParameters as $reflectionParameter) {
			if ($reflectionParameter instanceof \ReflectionParameter)
				$controllerReflectionParametersByName[$reflectionParameter->getName()] = $reflectionParameter;
		}
		return $controllerReflectionParametersByName;
	}
	
	/**
	 * if $var is null, this method will return true because null is from any type
	 *
	 * @param $var
	 * @param string $type
	 * @return bool
	 */
	private function verifyType($var, string $type): bool
	{
		$typeFunctions = [
			'boolean' => ['boolval', 'is_bool'],
			'bool' => ['boolval', 'is_bool'],
			'integer' => ['intval', 'is_int'],
			'int' => ['intval', 'is_int'],
			'float' => ['floatval', 'is_float'],
			'double' => ['doubleval', 'is_float'],
			'string' => ['strval', 'is_string'],
			'array' => ['is_array'],
			'resource' => ['is_resource']
		];
		
		$isSameType = false;
		if (array_key_exists($type, $typeFunctions)) {
			$isSameType = call_user_func($typeFunctions[$type][0], $var);
			if (count($typeFunctions[$type]) == 2)
				$isSameType = call_user_func($typeFunctions[$type][1], $isSameType);
		}
		return $isSameType;
	}
}