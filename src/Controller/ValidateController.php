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
     * @return array
     * @throws ClientException
     * @throws PlatformException
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
        return $this->route['parameters'][$myParameter->getName()]
            && array_key_exists('default', $this->route['parameters'][$myParameter->getName()]);
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
            'boolean'   => ['boolval', 'is_bool'],
            'bool'      => ['boolval', 'is_bool'],
            'integer'   => ['intval', 'is_int'],
            'int'       => ['intval', 'is_int'],
            'float'     => ['floatval', 'is_float'],
            'double'    => ['doubleval', 'is_float'],
            'string'    => ['strval', 'is_string'],
            'array'     => ['is_array'],
            'resource'  => ['is_resource']
        );
    
        $isSameType = is_null($var);
        if (!$isSameType && array_key_exists($type, $typeFunctions)) {
            $isSameType = call_user_func($typeFunctions[$type][0], $var);
            if (count($typeFunctions[$type]) == 2)
                $isSameType = call_user_func($typeFunctions[$type][1], $isSameType);
        }
        return $isSameType;
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