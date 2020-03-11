<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 03/11/2019
 * Time: 16:37
 */

namespace ThatsIt\Response;

use Mustache_Engine;

/**
 * Class Component
 * @package ThatsIt\Response
 *
 * It is supposed to use in ThatsIt\Response\View
 */
abstract class Component
{
    /**
     * @var int
     */
    private $id;
    
    /**
     * @var string
     */
    private $styles;
    
    /**
     * @var string
     */
    private $scriptsBeforeContent;
    
    /**
     * @var string
     */
    private $mainContent;
    
    /**
     * @var string
     */
    private $scriptsAfterContent;
    
    /**
     * @var Mustache_Engine|null
     */
    private static $mustacheEngine = null;
    
    /**
     * Component constructor.
     * @param string $styles
     * @param string $scriptsBeforeContent
     * @param string $mainContent
     * @param string $scriptsAfterContent
     */
    public function __construct(string $styles = "", string $scriptsBeforeContent = "",
                                string $mainContent = "", string $scriptsAfterContent = "")
    {
        $this->id = $this->getId();
        $this->styles = $styles;
        $this->scriptsBeforeContent = $scriptsBeforeContent;
        $this->mainContent = $mainContent;
        $this->scriptsAfterContent = $scriptsAfterContent;
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        $this->id = ($this->id === null ? rand() : $this->id);
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getStyles(): string
    {
        return $this->styles;
    }
    
    /**
     * @return string
     */
    public function getScriptsBeforeContent(): string
    {
        return $this->scriptsBeforeContent;
    }
    
    /**
     * @return string
     */
    public function getMainContent(): string
    {
        return $this->mainContent;
    }
    
    /**
     * @return string
     */
    public function getScriptsAfterContent(): string
    {
        return $this->scriptsAfterContent;
    }
    
    /**
     * @return Mustache_Engine
     */
    private static function getMustacheEngine(): Mustache_Engine
    {
        if (self::$mustacheEngine === null)
            self::$mustacheEngine = new Mustache_Engine();
        
        return self::$mustacheEngine;
    }
    
    /**
     * @param string $template - mustache html with mustache variables
     * @param array $context - array with variables used in $template
     *                         array key - name of the variable
     *                         array value - value of the variable
     * @return string
     */
    public function render(string $template, array $context): string
    {
        return self::getMustacheEngine()->render($template, $context);
    }
    
    /**
     * @param array $variables
     * @return string
     */
    abstract public function getContent(array $variables = []): string;
}