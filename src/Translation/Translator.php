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
	private $translationFilename;
	
	/**
	 * @var null|array
	 */
	private $translationFile;
	
	/**
	 * Translator constructor.
	 * @param string $translationFilename
	 * @param string $locale
	 */
	public function __construct(string $translationFilename, string $locale)
	{
		$this->locale = strtolower($locale);
		$this->translationFilename = $translationFilename;
		$this->translationFile = null;
	}
	
	/**
	 * @return string
	 */
	public function getTranslationFilename(): string
	{
		return $this->translationFilename;
	}
	
	/**
	 * @param string $translationFilename
	 * @return Translator
	 */
	public function setTranslationFilename(string $translationFilename): self
	{
		if ($translationFilename != $this->translationFilename) {
			$this->translationFile = null;
			$this->translationFilename = $translationFilename;
		}
		return $this;
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
		if ($locale != $this->locale) {
			$this->translationFile = null;
			$this->locale = strtolower($locale);
		}
		return $this;
	}
    
    /**
     * Translate token for the respective language.
     * If there is no translation possible, token is returned
     *
     * Translations can have variables (ex: {{var1}}, with brackets {{}})
     * If you pass $variables, this variables will be replaced
     *
     * @param string $token
     * @param array $variables - key: placeholder of the variable
     * 							  value: value to replace the placeholder with
     * @return string
     * @throws PlatformException
     */
	public function translate(string $token, array $variables = []): string
	{
		if ($this->translationFile === null) {
			$translationFilePath = $this->getPathToTranslationFile($this->locale, $this->translationFilename);
			$translationFallbackFilePath = $this->getPathToTranslationFile(Configurations::getFallbackLocale(),
				$this->translationFilename);
			if (file_exists($translationFilePath))
				$this->translationFile = require($translationFilePath);
			else if (file_exists($translationFallbackFilePath))
				$this->translationFile = require($translationFallbackFilePath);
			else
				throw new PlatformException(
					'You must provide a valid translationFilename and a valid locale or a valid fallbackLocale at config/config.php.',
					PlatformException::ERROR_NOT_FOUND_DANGER
				);
		}
		
		$lowerToken = mb_strtolower($token);
		if (!is_array($this->translationFile) || !array_key_exists($lowerToken, $this->translationFile))
			return $token;
		
		$translatedText = $this->translationFile[$lowerToken];
		foreach ($variables as $placeholder => $value)
			$translatedText = str_replace('{{'.$placeholder.'}}', $value, $translatedText);
		return $translatedText;
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