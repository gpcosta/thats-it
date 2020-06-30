<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 28/06/2020
 * Time: 17:27
 */

namespace ThatsIt\Sanitizer;

/**
 * Class ArrayOfInputsToSanitize
 * @package ThatsIt\Sanitizer
 */
class ArrayOfInputsToSanitize implements IInputToSanitize
{
    /**
     * @var string
     */
    private $inputName;
    
    /**
     * @var array
     */
    private $inputsToSanitize;
    
    /**
     * ArrayOfInputsToSanitize constructor.
     * @param string $inputName
     * @param array $inputValues
     * @param int $sanitizer - available consts in Sanitizer
     */
    public function __construct(string $inputName, array $inputValues,
                                int $sanitizer = Sanitizer::SANITIZER_TEXT_ONLY)
    {
        $this->inputName = $inputName;
        $this->inputsToSanitize = $this->constructorHelper($inputValues, $sanitizer);
    }
    
    /**
     * @return string
     */
    public function getInputName(): string
    {
        return $this->inputName;
    }
    
    /**
     * Sanitize $inputs (a multidimensional array)
     *
     * @return array
     */
    public function getSanitizedInput(): array
    {
        return $this->getSanitizedArrayOfInputs($this->inputsToSanitize);
    }
    
    /**
     * Heavily inspired in https://gist.github.com/esthezia/5804445
     *
     * @param array $inputs
     * @return array
     */
    private function getSanitizedArrayOfInputs(array $inputs): array
    {
        $sanitizedInputs = [];
        foreach ($inputs as $inputToSanitizeName => $inputToSanitize) {
            if (is_array($inputToSanitize))
                $sanitizedInputs[$inputToSanitizeName] = $this->getSanitizedArrayOfInputs($inputToSanitize);
            else if ($inputToSanitize instanceof InputToSanitize)
                $sanitizedInputs[$inputToSanitize->getInputName()] = $inputToSanitize->getSanitizedInput();
        }
        return $sanitizedInputs;
    }
    
    /**
     * @param array $inputs
     * @param int $sanitizer
     * @return array
     */
    private function constructorHelper(array $inputs, int $sanitizer = Sanitizer::SANITIZER_TEXT_ONLY): array
    {
        $inputsToSanitize = [];
        foreach ($inputs as $inputName => $inputValue) {
            if (!is_array($inputValue) && !is_object($inputValue))
                $inputsToSanitize[$inputName] = new InputToSanitize($inputName, $inputValue, $sanitizer);
            if (is_array($inputValue))
                $inputsToSanitize[$inputName] = $this->constructorHelper($inputValue, $sanitizer);
        }
        return $inputsToSanitize;
    }
}