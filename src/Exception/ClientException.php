<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 12:02
 */

namespace ThatsIt\Exception;

use Throwable;

/**
 * Class ClientException - everything that can be caused by the end-user (client)
 * @package ThatsIt\Exception
 *
 * @IMPORTANT: almost every exception raised in code working with this framework
 *             should extends from this one. This exception will allow to access
 *             to 404 and 405 error pages when these codes are sent to the client
 *             and also allow to customize a message for 500 error page
 */
class ClientException extends \Exception
{
    /**
     * ClientException constructor.
     * @param string $message
     * @param int $code (http code status)
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}