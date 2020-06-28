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
     * This constant says that the input is supposed to be HTML
     * and the filter will certify that the html passed to application
     * is safe and valid
     *
     * This sanitizer provides XSS protection
     *
     * @note: Input is filtered with HTML Purifier, so please use it
     *        only when HTML input is expected. If only text is expected
     *        use FILTER_TEXT_ONLY because this filter is computationally expensive
     */
    const SANITIZER_HTML_SAFETY = 2;
    
    /**
     * This constant says that the input is supposed to be only text
     * So every character that could provide unsafe input is escaped
     * or replaced by safe input
     * (e.g: "<" => "&lt" or "/" => "\/")
     *
     * This sanitizer provides XSS protection
     *
     * @note: Input is filtered with htmlspecialchars() method
     */
    const SANITIZER_TEXT_ONLY = 3;
}