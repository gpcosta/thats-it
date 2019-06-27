<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 12:03
 */

namespace ThatsIt\Exception;

use Throwable;

/**
 * Class PlatformException
 * @package ThatsIt\Exception
 */
class PlatformException extends \Exception
{
    /**
     * some resource was not found, but probably was caused by going to a wrong url
     */
    const ERROR_NOT_FOUND_SOFT = 1;
    
    /**
     * some resource was not found, but in this case some page is not accessible
     */
    const ERROR_NOT_FOUND_DANGER = 1;
    
    /**
     * indicate problems with the response given by controller
     */
    const ERROR_RESPONSE = 2;
    
    /**
     * PlatformException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}