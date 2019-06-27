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
 * Class ClientException
 * @package ThatsIt\Exception
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