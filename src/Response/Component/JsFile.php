<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 11/03/2020
 * Time: 14:33
 */

namespace ThatsIt\Response\Component;

use ThatsIt\Exception\PlatformException;

/**
 * Class JsFile
 * @package ThatsIt\Response\Component
 */
class JsFile
{
    const JS_FILE_ASYNC = 'async';
    const JS_FILE_DEFER = 'defer';
    const JS_FILE_SYNC = '';
    
    /**
     * @var string
     */
    private $src;
    
    /**
     * @var string
     */
    private $load;
    
    /**
     * @var string
     */
    private $type;
    
    /**
     * JsFile constructor.
     * @param string $src
     * @param string $load
     * @param string $type
     * @throws PlatformException
     */
    public function __construct(string $src, string $load = self::JS_FILE_SYNC, string $type = 'text/javascript')
    {
        if (!in_array($load, [self::JS_FILE_ASYNC, self::JS_FILE_DEFER, self::JS_FILE_SYNC]))
            throw new PlatformException('Parameter $load cannot be a random string. Please choose one of the constants available.', 500);
        
        $this->src = $src;
        $this->load = $load;
        $this->type = $type;
    }
    
    /**
     * @return string
     */
    public function getSrc(): string
    {
        return $this->src;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return '<script '.$this->load.' src="'.$this->src.'" type="'.$this->type.'"></script>';
    }
}