<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 20:18
 */

namespace ThatsIt\Response;

use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;

/**
 * Class View
 * @package ThatsIt\View
 */
class View extends HttpResponse
{
    /**
     * @var string
     */
    private $viewToShow;
    
    /**
     * @var array
     */
    private $routes;
    
    /**
     * View constructor.
     * @param string $viewToShow
     */
    public function __construct(string $viewToShow)
    {
        $this->viewToShow = $viewToShow;
    }
    
    /**
     * @return string
     */
    public function getContent(): string
    {
        ob_start();
        extract($this->variables);
        require_once(Folder::getSourceFolder().'/View/'.$this->viewToShow.'.php');
        return ob_get_clean();
    }
}