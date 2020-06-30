<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 28/06/2020
 * Time: 17:15
 */

namespace ThatsIt\Sanitizer;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Class InputToSanitize
 * @package ThatsIt\Sanitizer
 */
class InputToSanitize implements IInputToSanitize
{
    /**
     * @var string
     */
    private $inputName;
    
    /**
     * @var string
     */
    private $inputValue;
    
    /**
     * @var int
     */
    private $sanitizer;
    
    /**
     * InputToSanitize constructor.
     * @param string $inputName
     * @param $inputValue - can be every primitive type, except arrays and objects
     * @param int $sanitizer
     */
    public function __construct(string $inputName, $inputValue, int $sanitizer)
    {
        if (is_array($inputValue) || is_object($inputValue))
            throw new \InvalidArgumentException('Argument inputValue should not be an array or an object.');
        
        $this->inputName = $inputName;
        $this->inputValue = $inputValue;
        $this->sanitizer = $sanitizer;
    }
    
    /**
     * @return string
     */
    public function getInputName(): string
    {
        return $this->inputName;
    }
    
    /**
     * @return string
     */
    public function getSanitizedInput(): string
    {
        switch ($this->sanitizer) {
            case Sanitizer::SANITIZER_NONE:
                return $this->inputValue;
            case Sanitizer::SANITIZER_HTML_SAFETY:
                $config = HTMLPurifier_Config::createDefault();
                $purifier = new HTMLPurifier($config);
                return $purifier->purify($this->inputValue);
            case Sanitizer::SANITIZER_TEXT_ONLY:
                $sanitizedInput = htmlspecialchars(trim($this->inputValue), ENT_QUOTES);
                // remove weird spaces
                return preg_replace('/[\r\n\t\f\v]/', ' ', $sanitizedInput);
            default:
                throw new \InvalidArgumentException('Argument filter must be one of the following: '.
                    'Filter::FILTER_NONE, Filter::FILTER_HTML_SAFETY or Filter::FILTER_TEXT_ONLY');
        }
    }
}