<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 11/03/2020
 * Time: 14:10
 */

namespace ThatsIt\Response\Component;

/**
 * Class AppComponent
 * @package ThatsIt\Response\Component
 */
abstract class AppComponent extends Component
{
    /**
     * AppComponent constructor.
     * @param array $myComponents
     * @param array $cssFiles
     * @param string $innerCss
     * @param array $jsFilesBeforeBody
     * @param string $jsBeforeBody
     * @param array $jsFilesAfterBody
     * @param string $jsAfterBody
     */
    public function __construct(array $myComponents,
                                array $cssFiles = [], string $innerCss = "",
                                array $jsFilesBeforeBody = [], string $jsBeforeBody = "",
                                array $jsFilesAfterBody = [], string $jsAfterBody = "")
    {
        parent::__construct($myComponents, $cssFiles, $innerCss,
            $jsFilesBeforeBody, $jsBeforeBody, $jsFilesAfterBody, $jsAfterBody);
    }
    
    /**
     * @return string
     */
    private function returnCssInHTML(): string
    {
        $cssInHTML = '';
        foreach ($this->getCssFiles() as $cssFile)
            $cssInHTML .= $cssFile;
        return $cssInHTML;
    }
    
    /**
     * @return string
     */
    private function returnJsBeforeBodyInHTML(): string
    {
        $jsInHTML = '';
        foreach ($this->getJsFilesBeforeBody() as $jsFile)
            $jsInHTML .= $jsFile;
        return $jsInHTML;
    }
    
    /**
     * @return string
     */
    private function returnJsAfterBodyInHTML(): string
    {
        $jsInHTML = '';
        foreach ($this->getJsFilesAfterBody() as $jsFile)
            $jsInHTML .= $jsFile;
        return $jsInHTML;
    }
    
    /**
     * @param string $body
     * @param string $title
     * @param string $description
     * @param string $metaInfo
     * @return string
     *
     * @NOTE: $metaInfo will have all tags to populate the <head>
     *        except css and js code and files
     */
    public function renderHTML(string $body = '', string $title = '', string $description = '', string $metaInfo = ''): string
    {
        return <<< HTML
            <!DOCTYPE html>
            <html>
                <head>
                    <title>{$title}</title>
                    <meta name="description" content="{$description}">
                    {$metaInfo}
                    {$this->returnCssInHTML()}
                    {$this->getInnerCss()}
                    {$this->returnJsBeforeBodyInHTML()}
                    {$this->getJsBeforeBody()}
                </head>
                <body>
                    {$body}
                    {$this->returnJsAfterBodyInHTML()}
                    <script>
                        window.onload = function() {
                            {$this->getJsAfterBody()}
                        }
                    </script>
                </body>
            </html>
HTML;
    }
}