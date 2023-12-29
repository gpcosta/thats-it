<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 01/11/2023
 * Time: 18:01
 */

namespace ThatsIt\Response;


class FileResponse extends HttpResponse
{
    /**
     * @var string
     */
    private $fileName;
    
    /**
     * @var string
     */
    private $path;
    
    /**
     * FileResponse constructor.
     * @param string $fileName
     * @param string $path
     * @param null|string $mimeType
     */
    public function __construct(string $fileName, string $path, ?string $mimeType = null)
    {
        $this->fileName = $fileName;
        $this->path = $path;
        if (file_exists($this->path)) {
            $this->setHeader('Content-Type', $mimeType === null ? mime_content_type($this->path) : $mimeType);
            $this->setHeader('Content-Transfer-Encoding', 'Binary');
            //$this->setHeader('Content-Length', filesize($this->path));
            $this->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        }
    }
    
    /**
     * Echo the body content.
     */
    public function sendContent(): void
    {
        if (file_exists($this->path))
            readfile($this->path);
        else
            echo 'File not found.';
    }
}