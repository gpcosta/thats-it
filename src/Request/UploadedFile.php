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
    
    /**
     * Returns a user-friendly error message
     *
     * @return string
     */
    public function getUserErrorMessage(): string
    {
        switch ($this->errorCode) {
            case UPLOAD_ERR_OK:
                return 'The file "'.$this->getFilename().'" was uploaded with success';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file "'.$this->getFilename().'"  exceeds the allowed size.';
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE:
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
            default:
                return 'There was an error while uploading the file "'.$this->getFilename().'".';
        }
    }
    
    /**
     * Returns a dev prepared error message. The same that is in PHP site
     * @see https://www.php.net/manual/en/features.file-upload.errors.php
     *
     * @return string
     */
    public function getDevErrorMessage(): string
    {
        $message = 'File: "'.$this->getFilename().'". ';
        switch ($this->errorCode) {
            case UPLOAD_ERR_OK:
                return $message.'There is no error, the file uploaded with success.';
            case UPLOAD_ERR_INI_SIZE:
                return $message.'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
            case UPLOAD_ERR_FORM_SIZE:
                return $message.'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
            case UPLOAD_ERR_PARTIAL:
                return $message.'The uploaded file was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return $message.'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return $message.'Missing a temporary folder. Introduced in PHP 5.0.3.';
            case UPLOAD_ERR_CANT_WRITE:
                return $message.'Failed to write file to disk. Introduced in PHP 5.1.0.';
            case UPLOAD_ERR_EXTENSION:
                return $message.'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.';
            default:
                return $message.'There was an unknown error while uploading.';
        }
    }
}