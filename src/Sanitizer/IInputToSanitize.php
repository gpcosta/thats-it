<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 28/06/2020
 * Time: 17:15
 */

namespace ThatsIt\Sanitizer;

/**
 * Class IInputToSanitize
 * @package ThatsIt\Sanitizer
 */
interface IInputToSanitize
{
    /**
     * @return string - name of input
     */
    function getInputName(): string;
    
    /**
     * @return mixed - children can return whatever they want
     */
    function getSanitizedInput();
}