<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 11/03/2020
 * Time: 14:10
 */

namespace ThatsIt\Response\Component;

use ThatsIt\Response\Component;

/**
 * Class AppComponent
 * @package ThatsIt\Response\Component
 */
abstract class AppComponent extends Component
{
    public function __construct(string $mainContent, array $components = [],
                                array $cssFiles = [], string $innerCss = "",
                                array $jsFilesBeforeBody = [], string $jsBeforeBody = "",
                                array $jsFilesAfterBody = [], string $jsAfterBody = "")
    {
        parent::__construct($mainContent, $components, $cssFiles, $innerCss,
            $jsFilesBeforeBody, $jsBeforeBody, $jsFilesAfterBody, $jsAfterBody);
    }
    
    /**
     * @return string
     */
    private function returnCssInHTMLForm(): string
    {
        $cssInHTMLForm = '';
        foreach ($this->getCssFiles() as $cssFile)
            $cssInHTMLForm .= $cssFile;
        return $cssInHTMLForm;
    }
    
    /**
     * @return string
     */
    private function returnJsBeforeBodyInHTMLForm(): string
    {
        $jsInHTMLForm = '';
        foreach ($this->getJsFilesBeforeBody() as $jsFile)
            $jsInHTMLForm .= $jsFile;
        return $jsInHTMLForm;
    }
    
    /**
     * @return string
     */
    private function returnJsAfterBodyInHTMLForm(): string
    {
        $jsInHTMLForm = '';
        foreach ($this->getJsFilesAfterBody() as $jsFile)
            $jsInHTMLForm .= $jsFile;
        return $jsInHTMLForm;
    }
    
    /**
     * @param array $context
     * @return string
     *
     * @NOTE: $context['metaInfo'] will have all tags to populate the <head>
     *        except css and js code and files
     */
    public function render(array $context): string
    {
        $metaInfo = (isset($context['metaInfo']) ? $context['metaInfo'] : '');
        return <<< HTML
            <!DOCTYPE html>
            <html>
                <head>
                    {$metaInfo}
                    {$this->returnCssInHTMLForm()}
                    {$this->getInnerCss()}
                    {$this->returnJsBeforeBodyInHTMLForm()}
                    {$this->getJsBeforeBody()}
                </head>
                <body>
                    {$this->getMainContent()}
                    {$this->returnJsAfterBodyInHTMLForm()}
                    {$this->getJsAfterBody()}
                </body>
            </html>
HTML;
    }
}