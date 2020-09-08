<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 07/09/2020
 * Time: 15:32
 */

namespace ThatsIt\Request;

/**
 * Class UploadedFile
 * @package ThatsIt\Request
 *
 * @see https://www.php.net/manual/en/features.file-upload.post-method.php
 */
class UploadedFile
{
    /**
     * @var string
     */
    private $filename;
    
    /**
     * @var string
     */
    private $mimeType;
    
    /**
     * @var int - size in bytes
     */
    private $size;
    
    /**
     * @var string
     */
    private $temporaryName;
    
    /**
     * @var int
     *
     * @see https://www.php.net/manual/en/features.file-upload.errors.php
     */
    private $errorCode;
    
    /**
     * UploadedFile constructor.
     * @param array $fileInfo
     */
    public function __construct(array $fileInfo)
    {
        $this->filename = array_key_exists('name', $fileInfo) ? $fileInfo['name'] : '';
        $this->mimeType = array_key_exists('type', $fileInfo) ? $fileInfo['type'] : '';
        $this->size = array_key_exists('size', $fileInfo) ? $fileInfo['size'] : 0;
        $this->temporaryName = array_key_exists('tmp_name', $fileInfo) ? $fileInfo['tmp_name'] : '';
        $this->errorCode = array_key_exists('error', $fileInfo) ? $fileInfo['error'] : UPLOAD_ERR_NO_FILE;
    }
    
    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
    
    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }
    
    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
    
    /**
     * @return string
     */
    public function getTemporaryName(): string
    {
        return $this->temporaryName;
    }
    
    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}