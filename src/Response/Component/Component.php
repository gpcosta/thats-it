<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 03/11/2019
 * Time: 16:37
 */

namespace ThatsIt\Response\Component;

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
    private $mainContent;
    
    /**
     * @var array
     */
    private $components;
    
    /**
     * @var array
     */
    private $cssFiles;
    
    /**
     * @var string
     */
    private $innerCss;
    
    /**
     * @var array
     */
    private $jsFilesBeforeBody;
    
    /**
     * @var string
     */
    private $jsBeforeBody;
    
    /**
     * @var array
     */
    private $jsFilesAfterBody;
    
    /**
     * @var string
     */
    private $jsAfterBody;
    
    /**
     * @var Mustache_Engine|null
     */
    private static $mustacheEngine = null;
    
    /**
     * Component constructor.
     * @param string $mainContent
     * @param array $components
     * @param array $cssFiles
     * @param string $innerCss
     * @param array $jsFilesBeforeBody
     * @param string $jsBeforeBody
     * @param array $jsFilesAfterBody
     * @param string $jsAfterBody
     */
    public function __construct(string $mainContent, array $components = [],
                                array $cssFiles = [], string $innerCss = "",
                                array $jsFilesBeforeBody = [], string $jsBeforeBody = "",
                                array $jsFilesAfterBody = [], string $jsAfterBody = "")
    {
        $this->id = $this->getId();
        $this->mainContent = $mainContent;
        $this->components = $components;
        $this->cssFiles = $cssFiles;
        $this->innerCss = $innerCss;
        $this->jsFilesBeforeBody = $jsFilesBeforeBody;
        $this->jsBeforeBody = $jsBeforeBody;
        $this->jsFilesAfterBody = $jsFilesAfterBody;
        $this->jsAfterBody = $jsAfterBody;
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
     * Returns its own main content (only html from this component)
     *
     * @return string
     */
    public function getMainContent(): string
    {
        return $this->mainContent;
    }
    
    /**
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }
    
    /**
     * Returns all css files of this component and its children
     *
     * @return array
     */
    public function getCssFiles(): array
    {
        $cssFiles = [];
        foreach ($this->cssFiles as $cssFile) {
            if ($cssFile instanceof CssFile)
                $cssFiles[$cssFile->getHref()] = $cssFile;
        }
        
        foreach ($this->components as $component) {
            if ($component instanceof Component) {
                foreach ($component->getCssFiles() as $cssFile) {
                    if ($cssFile instanceof CssFile)
                        $cssFiles[$cssFile->getHref()] = $cssFile;
                }
            }
        }
        
        return $cssFiles;
    }
    
    /**
     * Returns all inner css of this component and its children
     *
     * @return string
     */
    public function getInnerCss(): string
    {
        $innerCss = $this->innerCss;
        foreach ($this->components as $component) {
            if ($component instanceof Component)
                $innerCss .= $component->getInnerCss();
        }
        return $innerCss;
    }
    
    /**
     * Returns all js files before body of this component and its children
     *
     * @return array
     */
    public function getJsFilesBeforeBody(): array
    {
        $jsFiles = [];
        foreach ($this->jsFilesBeforeBody as $jsFile) {
            if ($jsFile instanceof JsFile)
                $jsFiles[$jsFile->getSrc()] = $jsFile;
        }
    
        foreach ($this->components as $component) {
            if ($component instanceof Component) {
                foreach ($component->getJsFilesBeforeBody() as $jsFile) {
                    if ($jsFile instanceof JsFile)
                        $jsFiles[$jsFile->getSrc()] = $jsFile;
                }
            }
        }
    
        return $jsFiles;
    }
    
    /**
     * Returns all js before body of this component and its children
     *
     * @return string
     */
    public function getJsBeforeBody(): string
    {
        $js = $this->jsBeforeBody;
        foreach ($this->components as $component) {
            if ($component instanceof Component)
                $js .= $component->getJsBeforeBody();
        }
        return $js;
    }
    
    /**
     * Returns all js files after body of this component and its children
     *
     * @return array
     */
    public function getJsFilesAfterBody(): array
    {
        $jsFiles = [];
        foreach ($this->jsFilesAfterBody as $jsFile) {
            if ($jsFile instanceof JsFile)
                $jsFiles[$jsFile->getSrc()] = $jsFile;
        }
    
        foreach ($this->components as $component) {
            if ($component instanceof Component) {
                foreach ($component->getJsFilesAfterBody() as $jsFile) {
                    if ($jsFile instanceof JsFile)
                        $jsFiles[$jsFile->getSrc()] = $jsFile;
                }
            }
        }
    
        return $jsFiles;
    }
    
    /**
     * Returns all js after body of this component and its children
     *
     * @return string
     */
    public function getJsAfterBody(): string
    {
        $js = $this->jsAfterBody;
        foreach ($this->components as $component) {
            if ($component instanceof Component)
                $js .= $component->getJsAfterBody();
        }
        return $js;
    }
    
    /**
     * @return Mustache_Engine
     */
    public static function getMustacheEngine(): Mustache_Engine
    {
        if (self::$mustacheEngine === null)
            self::$mustacheEngine = new Mustache_Engine();
        
        return self::$mustacheEngine;
    }
    
    /**
     * @param array $context - array with variables used in $mainContent
     *                         array key - name of the variable
     *                         array value - value of the variable
     * @return string
     */
    abstract public function render(array $context): string;
}