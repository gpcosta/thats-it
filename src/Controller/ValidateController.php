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
use ThatsIt\NullObject;
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
	private $populatedParameters;
    
    /**
     * ValidateController constructor.
     * @param HttpRequest $request
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
		$this->givenParameters = $request->getAllParameters();
		$this->populatedParameters = $this->getPopulatedParameters();
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
			$args[] = $this->populatedParameters[$parameter->getName()];
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
			if (!array_key_exists($parameter->getName(), $this->populatedParameters))
				throw new PlatformException(
					'Controller method has parameters that were not passed.',
					400
				);
			
			$args[] = $this->populatedParameters[$parameter->getName()];
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
        
        $interceptResponseMethod = $this->reflectionController->getMethod('interceptResponse');
		return $interceptResponseMethod->invoke($this->controller, $response);
	}
	
	/**
	 * @return array
	 * @throws PlatformException
	 */
	private function getPopulatedParameters(): array
	{
		// validate if all asked parameters by Route ($this->routeParameters)
        // are matched by the request ($this->givenParameters)
        foreach ($this->routeParameters as $name => $infoAboutParameter) {
            // there is no given parameter and no default value
            if (!array_key_exists($name, $this->givenParameters) && !array_key_exists('default', $infoAboutParameter))
                throw new PlatformException(
                    "Given parameters are incompatible with Route parameters.",
                    PlatformException::ERROR_NOT_FOUND_DANGER
                );
        }
		
        // validate if all parameters on Controller constructor and Controller method ($controllerReflectionParametersByName)
        // are on the request ($this->givenParameters)
        // populate $populatedParameters with the right values
        //      - first, try to obtain the value of the parameter from $this->givenParameters
        //      - then, if not found in $this->givenParameters, try to obtain the value of the parameter from
        //        $this->routeParameters (default value)
        //      - last, if not found in any of the previous steps, verify if the Controller constructor
        //        or Controller method has such parameter as optional
        $controllerReflectionParametersByName = $this->getControllerReflectionParametersByName();
        $populatedParameters = [];
		foreach ($controllerReflectionParametersByName as $name => $reflectionParameter) {
            // verify if controller contructor or method parameters exists in route parameters
            if (!array_key_exists($name, $this->routeParameters))
                throw new PlatformException(
                    "Route parameters are incompatible with Controller parameters.",
                    PlatformException::ERROR_NOT_FOUND_DANGER
                );
            
            $populatedParameters[$name] = new NullObject();
            // parameter provided in request
            if (array_key_exists($name, $this->givenParameters))
                $populatedParameters[$name] = $this->givenParameters[$name];
            // not provided in request but it uses the default value of the parameter
            else if (is_array($this->routeParameters[$name]) && array_key_exists('default', $this->routeParameters[$name]))
                $populatedParameters[$name] = $this->routeParameters[$name]['default'];
            // not in request and there is no default, but the parameter is optional in called Controller method
            else if ($reflectionParameter->isOptional())
                $populatedParameters[$name] = $reflectionParameter->getDefaultValue();
            
            // there is no value for the parameter with name = $name
            if ($populatedParameters[$name] instanceof NullObject)
                throw new PlatformException(
                    "'$name' parameter is not populated. The request must provide such parameter.",
                    PlatformException::ERROR_NOT_FOUND_DANGER
                );
            
            $expectedType = $reflectionParameter->getType();
            // verify type of parameter
            if ($expectedType !== null && !$this->verifyType($populatedParameters[$name], $expectedType->getName()))
                throw new PlatformException(
                    "'$name' parameter should be from " . $expectedType->getName() . " type.",
                    PlatformException::ERROR_NOT_FOUND_DANGER
                );
        }
        return $populatedParameters;
        
        /*$correctParameters = [];
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
		return $correctParameters;*/
	}
	
	/**
	 * Get reflection parameters indexed by name of both constructor and method from controller
	 *
	 * @return \ReflectionParameter[]
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
		
		/** @var \ReflectionParameter[] $controllerReflectionParametersByName */
		$controllerReflectionParametersByName = [];
		foreach ($controllerReflectionParameters as $reflectionParameter)
			$controllerReflectionParametersByName[$reflectionParameter->getName()] = $reflectionParameter;
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