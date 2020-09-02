<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 23/08/2020
 * Time: 17:11
 */

namespace ThatsIt\Translation;

use ThatsIt\Request\HttpRequest;

/**
 * Class BrowserLanguage
 * @package ThatsIt\Translation
 *
 * @note: important read to understand how Browser language works (HTTP_ACCEPT_LANGUAGE)
 *        this class is strongly inspired in https://www.codingwithjesse.com/blog/use-accept-language-header/
 */
class BrowserLanguage
{
    /**
     * @var array - array sorted by q factor (importance for the browser)
     *              key: language code
     *              value: q - suitability factor of this language
     */
    private $languages;
    
    /**
     * BrowserLanguage constructor.
     * @param HttpRequest $request
     * @throws \ThatsIt\Request\Exception\MissingRequestMetaVariableException
     */
    public function __construct(HttpRequest $request)
    {
        $this->languages = [];
        // break up string into pieces (languages and q factors)
        preg_match_all(
            '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            $request->getBrowserLanguage(),
            $langParse
        );
    
        if (count($langParse[1])) {
            // create a list like "en" => 0.8
            $this->languages = array_combine($langParse[1], $langParse[4]);
        
            // set default to 1 for any without q factor
            foreach ($this->languages as $lang => $val) {
                if ($val === '') $this->languages[$lang] = 1;
            }
        
            // sort list based on value
            arsort($this->languages, SORT_NUMERIC);
        }
    }
    
    /**
     * @return array
     */
    public function getAllBrowserLanguages(): array
    {
        // look through sorted list and use first one that matches our languages
        $validLanguages = [];
        foreach ($this->languages as $lang => $val)
            $validLanguages[] = $lang;
        return $validLanguages;
    }
    
    /**
     * @param int $importance - from 1 to infinite
     *                          0 get most appropriate language for the browser
     *                          and with larger $importance less appropriate is the language returned
     * @return null|string - ISO 639-1 code for the respective language is returned
     *                       when $importance is bigger than the size of all appropriate languages,
     *                       it will be returned null
     */
    public function getISOLanguageByImportance(int $importance): ?string
    {
        $lang = $this->getFullLanguageByImportance($importance);
        return ($lang ? substr($lang, 0, 2) : null);
    }
    
    /**
     * @param int $importance - from 1 to infinite
     *                          0 get most appropriate language for the browser
     *                          and with larger $importance less appropriate is the language returned
     * @return null|string - full code for the respective language is returned
     *                       when $importance is bigger than the size of all appropriate languages,
     *                       it will be returned null
     */
    public function getFullLanguageByImportance(int $importance): ?string
    {
        $importance--;
        $i = 0;
        foreach ($this->languages as $lang => $val) {
            if ($importance == $i)
                return $lang;
        }
        return null;
    }
    
    /**
     * Will get the most appropriate language (ISO 639-1) from the available ones
     *
     * @param array $availableLanguages
     * @return null|string
     */
    public function getMostAppropriateISOLanguage(array $availableLanguages): ?string
    {
        $lang = $this->getMostAppropriateFullLanguage($availableLanguages);
        return ($lang ? substr($lang, 0, 2) : null);
    }
    
    /**
     * Will get the most appropriate language (full code) from the available ones
     *
     * @param array $availableLanguages
     * @return null|string
     */
    public function getMostAppropriateFullLanguage(array $availableLanguages): ?string
    {
        foreach ($this->languages as $lang => $val) {
            foreach ($availableLanguages as $availableLanguage) {
                if (strpos($lang, $availableLanguage) === 0)
                    return $lang;
            }
        }
        return null;
    }
    
    /**
     * @param string $language
     * @return bool
     */
    public function isBrowserLanguage(string $language): bool
    {
        foreach ($this->languages as $lang => $val) {
            if (strpos($lang, $language) === 0)
                return true;
        }
        return false;
    }
}