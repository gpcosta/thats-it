<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 11/03/2020
 * Time: 14:32
 */

namespace ThatsIt\Response\Component;

/**
 * Class CssFile
 * @package ThatsIt\Response\Component
 */
class CssFile
{
    /**
     * @var string
     */
    private $href;
    
    /**
     * @var bool
     */
    private $isToLoadImmediately;
    
    /**
     * @var string
     */
    private $rel;
    
    /**
     * @var string
     */
    private $type;
    
    /**
     * CssFile constructor.
     * @param string $href
     * @param bool $isToLoadImmediately
     * @param string $rel
     * @param string $type
     */
    public function __construct(string $href, bool $isToLoadImmediately = true, string $rel = 'stylesheet',
                                string $type = 'text/css')
    {
        $this->href = $href;
        $this->isToLoadImmediately = $isToLoadImmediately;
        $this->rel = $rel;
        $this->type = $type;
    }
    
    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return '<link rel="'.$this->rel.'" type="'.$this->type.'" href="'.$this->href.'"'.
            ($this->isToLoadImmediately ? '' : ' media="all" onload="if(media!=\'all\')media=\'all\'"').'>';
    }
}