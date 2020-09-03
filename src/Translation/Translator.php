<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 16/08/2020
 * Time: 17:16
 */

namespace ThatsIt\Translation;

use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;

/**
 * Class Translator
 * @package ThatsIt\Translation
 */
class Translator
{
    /**
     * @var string
     */
    private $locale;
    
    /**
     * @var string
     */
    private $translationFile;
    
    /**
     * Translator constructor.
     * @param string $translationFilename
     * @param string|null $locale
     * @throws PlatformException
     */
    public function __construct(string $translationFilename, string $locale = null)
    {
        $config = Configurations::getGeneralConfig();
        $translationFilePathFallback = '';
        $translationFilePath = '';
        
        if (array_key_exists('fallbackLocale', $config)) {
            $this->locale = $config['fallbackLocale'];
            $translationFilePathFallback = $this->getPathToTranslationFile($this->locale, $translationFilename);
        } else {
            throw new PlatformException(
                'You must provide a locale to Translator or define a fallbackLocale at config.php.',
                PlatformException::ERROR_CONFIG_LINE_MISSING
            );
        }
        
        if ($locale) {
            $this->locale = $locale;
            $translationFilePath = $this->getPathToTranslationFile($this->locale, $translationFilename);
        }
        
        if ($translationFilePath && file_exists($translationFilePath))
            $this->translationFile = require($translationFilePath);
        else if ($translationFilePathFallback && file_exists($translationFilePathFallback))
            $this->translationFile = require($translationFilePathFallback);
        else
            throw new PlatformException(
                'You must provide a valid translationFilename and '.
                'a valid locale to Translator or define a valid fallbackLocale at config.php.',
                PlatformException::ERROR_NOT_FOUND_DANGER
            );
    }
    
    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
    
    /**
     * @param string $locale
     * @return Translator
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }
    
    /**
     * Translate token for the respective language.
     * If there is no translation possible, token is returned
     *
     * @param string $token
     * @return string
     */
    public function translate(string $token): string
    {
        if (!is_array($this->translationFile) || !array_key_exists($token, $this->translationFile))
            return $token;
        return $this->translationFile[$token];
    }
    
    /**
     * @param string $locale
     * @param string $translationFilename
     * @return string
     */
    private function getPathToTranslationFile(string $locale, string $translationFilename): string
    {
        return Folder::getTranslationsFolder().'/'.$locale.'/'.$translationFilename.'.php';
    }
}