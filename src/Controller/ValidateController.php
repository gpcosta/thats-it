<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/06/2019
 * Time: 18:21
 */

namespace ThatsIt\Controller;

use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\PlatformException;

/**
 * Class ValidateController
 * @package ThatsIt\Controller
 */
class ValidateController
{
    /**
     * @var array
     */
    private $route;
    
    /**
     * @var string
     */
    private $controllerName;
    
    /**
     * @var string
     */
    private $functionName;
    
    /**
     * @var array
     */
    private $givenParameters;
    
    /**
     * ValidateController constructor.
     * @param array $route
     */
    public function __construct(array $route)
    {
        $this->route = $route;
        $this->controllerName = $route['controller'];
        $this->functionName = $route['function'];
        $this->givenParameters = ['parameters'];
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    public function verifyControllerAndFunction(): bool
    {
        $this->getReflectionMethod();
        return true;
    }
    
    /**
     * @param array $givenParameters
     * @param string $controllerName
     * @param string $functionName
     * @return array
     * @throws \Exception
     */
    public function getCorrectParameters(array $givenParameters): array
    {
        $correctParameters = array();
    
        $parametersInRealController = $this->getReflectionMethod()->getParameters();
        foreach ($parametersInRealController as $key => $parameter) {
            $givenParameter = isset($givenParameters[$parameter->getName()]) ?
                $givenParameters[$parameter->getName()] :
                null;
    
            if ($this->isParameterProvided($parameter, $givenParameters)) {
                // parameter provided but not from the same type of expected parameter
                if (!$this->isSameTypeFromProvidedParameter($parameter, $givenParameter)) {
                    throw new ClientException("Parameter " . $parameter->getName() .
                        ($parameter->hasType() ? " (" . $parameter->getType() . ")" : "") . " necessary.", 405);
                }
                
                $correctParameters[$parameter->getPosition()] = $givenParameters[$parameter->getName()];
            } else if ($this->hasDefault($parameter)) {
                // parameter not provided and the default parameter is not from the expected in controller
                if (!$this->isSameTypeFromDefaultParameter($parameter)) {
                    throw new ClientException("Default parameter " . $parameter->getName() .
                        " doesn't have the expected type.", 405);
                }
                
                $correctParameters[$parameter->getPosition()] =
                    $this->route['parameters'][$parameter->getName()]['default'];
            } else /*if (!$parameter->isOptional())*/ {
                throw new ClientException("There is no provided or default parameter.", 405);
            }
        }
        
        return $correctParameters;
    }
    
    /**
     * @return \ReflectionMethod
     * @throws PlatformException
     */
    private function getReflectionMethod(): \ReflectionMethod
    {
        try {
            $reflectionController = new \ReflectionClass($this->controllerName);
            $reflectionMethod = $reflectionController->getMethod($this->functionName);
            return $reflectionMethod;
        } catch (\ReflectionException $e) {
            throw new PlatformException("It was not possible to found the controller ".$this->controllerName.
                " or the method ".$this->functionName, PlatformException::ERROR_NOT_FOUND_DANGER);
        }
    }
    
    /**
     * @param \ReflectionParameter $myParameter
     * @param array $givenParameters
     * @return bool
     */
    private function isParameterProvided(\ReflectionParameter $myParameter, array $givenParameters): bool
    {
        return array_key_exists($myParameter->getName(), $givenParameters);
    }
    
    /**
     * @param \ReflectionParameter $myParameter
     * @return bool
     */
    private function hasDefault(\ReflectionParameter $myParameter): bool
    {
        return isset($this->route['parameters'][$myParameter->getName()]['default']);
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
        $typeFunctions = array(
            'boolean'   => 'is_bool',
            'bool'      => 'is_bool',
            'integer'   => 'is_int',
            'int'       => 'is_int',
            'float'     => 'is_float',
            'double'    => 'is_float',
            'string'    => 'is_string',
            'array'     => 'is_array',
            'resource'  => 'is_resource'
        );
        
        return is_null($var) || call_user_func($typeFunctions[$type], $var);
    }
    
    /**
     * @param \ReflectionParameter $myParameter
     * @return bool
     */
    private function isSameTypeFromDefaultParameter(\ReflectionParameter $myParameter): bool
    {
        if ($this->hasDefault($myParameter)) {
            return $myParameter->hasType()
                && $this->verifyType(
                    $this->route['parameters'][$myParameter->getName()]['default'],
                    $myParameter->getType()->getName()
                );
        }
        return false;
    }
    
    /**
     * @param \ReflectionParameter $myParameter
     * @param $givenParameter
     * @return bool
     */
    private function isSameTypeFromProvidedParameter(\ReflectionParameter $myParameter, $givenParameter): bool
    {
        return $myParameter->hasType() && $this->verifyType($givenParameter, $myParameter->getType()->getName());
    }
}