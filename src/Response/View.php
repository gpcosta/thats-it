<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 20:18
 */

namespace ThatsIt\Response;

use ThatsIt\Folder\Folder;
use ThatsIt\Response\Component\AppComponent;
use ThatsIt\Sanitizer\Sanitizer;

/**
 * Class View
 * @package ThatsIt\Response
 */
class View extends HttpResponse
{
    /**
     * @var string|null
     */
    private $pageToShow;
    
    /**
     * @var AppComponent|null
     */
    private $component;
    
    /**
     * View constructor.
     * @param $viewOrComponent - if it's a string, it is interpreted as the name of a view
     *                           else if it's a Component, it is used as Component
     * @param $sanitizer - which Sanitizer is used
     */
    public function __construct($viewOrComponent, int $sanitizer = Sanitizer::SANITIZER_HTML_ENCODE)
    {
		if ($viewOrComponent instanceof AppComponent) {
			$this->component = $viewOrComponent;
			$this->pageToShow = null;
		} else if (is_string($viewOrComponent)) {
			$this->pageToShow = $viewOrComponent;
			$this->component = null;
		}
		$this->setSanitizer($sanitizer);
		$this->setHeader('Content-Type', 'text/html;charset=utf-8');
    }
    
    public function sendContent(): void
    {
        $content = "";
        
        // if there is a page to show, it will show it
        if ($this->pageToShow) {
            extract($this->getSanitizedVariables());
            $t = function(string $token, array $variables = []) {
                if ($this->translator)
                    return $this->translator->translate($token, $variables);
                else
                    return $token;
            };
            ob_start();
            require_once(Folder::getSourceFolder().'/View/'.$this->pageToShow.'.php');
            $content = ob_get_clean();
        }
        // if there is a component, will print it
        else if ($this->component && $this->component instanceof AppComponent) {
            $content = $this->component->render();
        }
        echo $content;
    }
}