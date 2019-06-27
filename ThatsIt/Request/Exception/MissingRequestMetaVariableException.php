<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 27/06/2019
 * Time: 09:08
 */

namespace ThatsIt\Request\Exception;

use Throwable;

/**
 * Class MissingRequestMetaVariableException
 * @package ThatsIt\Request\Exception
 */
class MissingRequestMetaVariableException extends \Exception
{
    public function __construct($variableName, $code = 0, Throwable $previous = null) {
        $message = "Request meta-variable ".$variableName." was not set.";
        parent::__construct($message, $code, $previous);
    }
}