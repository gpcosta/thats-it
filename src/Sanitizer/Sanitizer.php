<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 28/06/2020
 * Time: 12:45
 */

namespace ThatsIt\Sanitizer;

/**
 * Class Sanitizer
 * @package ThatsIt\Sanitizer
 *
 * Class responsible for sanitizing inputs
 */
class Sanitizer
{
    /**
     * This constant sayst that no filter will be applied to input
     */
    const SANITIZER_NONE = 1;
	
	/**
	 * This constant says that the input is supposed to be only UTF8
	 * If any character is not utf8, this is "transformed" to utf8 encoding
	 */
	const SANITIZER_UTF8_ENCODE = 2;
    
    /**
     * This constant says that the input is supposed to be only HTML
     * So every character that could provide unsafe input is escaped
     * or replaced by safe input
     * (e.g: "<" => "&lt" or "/" => "\/")
     *
     * This sanitizer provides XSS protection
     *
     * @note: Input is filtered with htmlspecialchars() method
     */
    const SANITIZER_HTML_ENCODE = 3;
    
    /**
     * This constant says that the value is supposed to be HTML
     * and the filter will certify that the html passed is safe and valid
     *
     * This sanitizer provides XSS protection
     *
     * @note: Input is filtered with HTML Purifier, so please use it
     *        only when HTML input is expected. If only text is expected
     *        use SANITIZER_HTML_ENCODE because SANITIZER_ALLOW_HTML filter is computationally expensive
     */
    const SANITIZER_ALLOW_HTML = 4;
    
    /**
     * @param int $sanitizer - constant from above
     * @return mixed
     */
    public static function sanitize($variable, int $sanitizer)
    {
        if ($sanitizer === self::SANITIZER_NONE)
            return $variable;
        
        if (is_string($variable)) {
            $variable = self::getSanitizedValue($variable, $sanitizer);
        } else if (is_array($variable)) {
            foreach ($variable as $key => $value)
                $variable[$key] = self::sanitize($value, $sanitizer);
        } else if (is_object($variable)) {
            // iterate all public variables and sanitize all them
            foreach ($variable as $varName => $varValue)
                $variable->$varName = self::sanitize($varValue, $sanitizer);
        }
        
        return $variable;
    }
    
    /**
     * @param string $value
     * @param int $sanitizer - constant from above
     * @return string
     */
    private static function getSanitizedValue(string $value, int $sanitizer): string
    {
		switch ($sanitizer) {
			case self::SANITIZER_NONE:
				return $value;
			case self::SANITIZER_UTF8_ENCODE:
				if (mb_detect_encoding($value, 'UTF-8') == 'UTF-8')
					return $value;
				else
					return utf8_encode($value);
			case Sanitizer::SANITIZER_HTML_ENCODE:
				$sanitizedValue = htmlspecialchars(trim($value), ENT_QUOTES|ENT_HTML5, 'UTF-8');
				// remove weird spaces
				return preg_replace('/[\r\n\t\f\v]/', ' ', $sanitizedValue);
			case self::SANITIZER_ALLOW_HTML:
				$config = \HTMLPurifier_Config::createDefault();
				$purifier = new \HTMLPurifier($config);
				return $purifier->purify($value);
			default:
				throw new \InvalidArgumentException('Argument sanitizer must be one of the following: '.
					'Sanitizer::SANITIZER_NONE, Sanitizer::SANITIZER_UTF8_ENCODE, Sanitizer::SANITIZER_HTML_ENCODE '.
					'or Sanitizer::SANITIZER_ALLOW_HTML');
		}
    }
}