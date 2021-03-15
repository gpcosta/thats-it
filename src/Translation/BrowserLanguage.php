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
	 * @var array - array sorted by q factor (importance for the browser)
	 *              key: country code
	 *              value: q - suitability factor of this language
	 *
	 * @note: countries in this array are based on languages from $request->getBrowserLanguage()
	 */
    private $countries;
    
    /**
     * BrowserLanguage constructor.
     * @param HttpRequest $request
     * @throws \ThatsIt\Request\Exception\MissingRequestMetaVariableException
     */
    public function __construct(HttpRequest $request)
    {
        $this->languages = [];
        $this->countries = [];
        // break up string into pieces (languages and q factors)
        preg_match_all(
            '/(([a-z]{1,8})(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            $request->getBrowserLanguage(),
            $langParse
        );
	
		// $langParse[0] - each element is like 'pt;q=0.9' or 'pt-BR;q=0.8' or 'pt-PT'
		// $langParse[1] - each element is like 'pt' or 'pt-BR' or 'pt-PT'
		// $langParse[2] - each element is like 'pt' or 'pt' or 'pt'
		// $langParse[3] - each element is like '' or '-BR' or '-PT'
		// $langParse[4] - each element is like ';q=0.9' or ';q=0.8' or ''
		// $langParse[5] - each element is like '0.9' or '0.8' or ''
		for ($i = 0, $len = count($langParse[0]); $i < $len; $i++) {
			$value = (int)($langParse[5][$i] == '' ? 1 : $langParse[5][$i]);
			$this->languages[strtolower($langParse[2][$i])] = $value;
			$this->countries[($langParse[3][$i] ? substr($langParse[3][$i], 1) : $langParse[2][$i])] = $value;
		}
		// sort lists based on value
		arsort($this->languages, SORT_NUMERIC);
		arsort($this->countries, SORT_NUMERIC);
    }
	
	/**
	 * Will get the most appropriate language (ISO 639-1) from the available ones
	 *
	 * @param array $availableLanguages - if is passed an empty array, the language with
	 * 									   the most suitable factor is returned (bigger q factor)
	 * @param null|string $defaultValue
	 * @return null|string
	 */
    public function getMostAppropriateISOLanguage(array $availableLanguages = [], ?string $defaultValue = null): ?string
    {
    	// passing languages ordered by "q" factor
		return $this->getFirstAvailableFrom(array_keys($this->languages), $availableLanguages, $defaultValue);
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
		$importance--;
		$i = 0;
		foreach ($this->languages as $lang => $val) {
			if ($importance == $i)
				return $lang;
		}
		return null;
	}
	
	/**
	 * Will get the most appropriate country (ISO 639-1) from the available ones
	 * @note: the country returned is based on languages from $request->getBrowserLanguage()
	 * 		  it is not the most reliable method to get current country of the user
	 *
	 * @param array $availableCountries - if is passed an empty array, the country with
	 * 									   the most suitable factor is returned (bigger q factor)
	 * @param null|string $defaultValue
	 * @return null|string
	 */
    public function getMostAppropriateISOCountryBasedOnLanguage(array $availableCountries = [],
																 ?string $defaultValue = null): ?string
	{
		// passing countries ordered by "q" factor
		return $this->getFirstAvailableFrom(array_keys($this->countries), $availableCountries, $defaultValue);
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
	
	/**
	 * Method to get first value from $fromValues array that are in $searchValues array
	 * If no value is found, $defaultValue is returned
	 *
	 * @param array $fromValues
	 * @param array $searchValues
	 * @param null|string $defaultValue
	 * @return null|string
	 */
    private function getFirstAvailableFrom(array $fromValues, array $searchValues,
										    ?string $defaultValue = null): ?string
	{
		$thereIsSearchValues = (bool) count($searchValues);
		
		foreach ($fromValues as $val) {
			// if $searchValues is empty array, the first value from $fromValues is returned
			if (!$thereIsSearchValues)
				return $val;
			
			foreach ($searchValues as $searchValue) {
				if (strpos($val, $searchValue) === 0)
					return $val;
			}
		}
		return $defaultValue;
	}
}