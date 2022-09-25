<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 05/07/2019
 * Time: 19:33
 */

namespace ThatsIt\FunctionsBag;

use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\PlatformException;

/**
 * Class FunctionsBag
 * @package ThatsIt\FunctionsBag
 *
 * this class is a bag for functions that will be needed in more than one place
 */
class FunctionsBag
{
	/**
	 * @var null|string
	 */
	private static $routesPath = null;
	
	/**
	 * @var string
	 */
	private static $httpHost = "";
	
	/**
	 * @return null|string
	 */
	public static function getRoutesPath(): ?string
	{
		return self::$routesPath;
	}
	
	/**
	 * @param string $routesPath
	 */
	public static function setRoutesPath(string $routesPath): void
	{
		self::$routesPath = $routesPath;
	}
	
	/**
	 * @return string
	 */
	public static function getHttpHost(): string
	{
		return self::$httpHost;
	}
	
	/**
	 * @param string $httpHost
	 */
	public static function setHttpHost(string $httpHost): void
	{
		self::$httpHost = $httpHost;
	}
    
    /**
     * @param string $str
     * @return string
     */
    public static function getStringIntoValidUrl(string $str): string
    {
        $str = self::removeAccents($str);
        // replace spaces by "-"
        $str = str_replace(' ', '-', $str);
        // remove remaining weird characters
        return strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $str));
    }
	
	
	/**
	 * @param string $name
	 * @param array $variables [name => value]
	 * @param bool $withOptional (url with optional part or not. when there is no optional part, doesn't matter its value)
     *                           - this parameter is deprecated and any value that is passed here is ignored
	 * @param bool $addSchemeAndHost (if true, will add the scheme and host to the url)
	 * @return string
	 * @throws PlatformException
	 */
	public static function getUrl(string $name, array $variables = [], bool $withOptional = false,
								  bool $addSchemeAndHost = false): string
	{
		static $routes;
		
		// just to load routes once
		if (!$routes) $routes = Configurations::getRoutesConfig(self::$routesPath);
		
		if (!isset($routes[$name])) {
			throw new PlatformException("There are no url for '" . $name . "'",
				PlatformException::ERROR_NOT_FOUND_DANGER);
		}
		
		$path = $routes[$name]['path'];
		
		//if ($withOptional) {
			// obtain all optional groups, even the ones inside other optinal groups
			// ex: /{urlCode}[/{orderType}[/{menuId}[/{productId}]]]
			$initialGroupOfOptionals = self::getGroupOfOptionalsInUrl($path);
		//}
        
		// save variables names that were already used in url
		$alreadyUsedVariablesInPath = [];
		
		// will substitute all variables for their value
		foreach ($variables as $name => $value) {
			// arrays are only used as GET (or query) parameters
			if (is_array($value))
				continue;
			
			if ($value) {
                // put the value of the variable in a valid string to be part of the url path
                $value = self::removeAccents($value);
                $value = str_replace(' ', '-', $value);
                // remove remaining weird characters
                $value = preg_replace('/[^a-zA-Z0-9_-]/', '', $value);
                
                // limit: -1 is the same as no limit; $count will know how many replaces happened
                $path = preg_replace("/\{" . $name . "(\:.*){0,1}\}/U", $value, $path, -1, $count);
                // if any replace happened, this variable is already used
                // if not, this variable should be added to path as a GET parameter
                if ($count)
                    $alreadyUsedVariablesInPath[] = $name;
			}
		}
		
		// remove all variables already set in url (this step was not made in last foreach just as a safety measure)
		foreach ($alreadyUsedVariablesInPath as $variableName) unset($variables[$variableName]);
		
		//if ($withOptional) {
			$finalGroupOfOptionals = self::getGroupOfOptionalsInUrl($path);
			$biggestEqualGroup = -1;
			for ($i = count($finalGroupOfOptionals) - 1; $i >= 0; $i--) {
				if ($initialGroupOfOptionals[$i] == $finalGroupOfOptionals[$i])
					$biggestEqualGroup = $i;
			}
			
			if ($biggestEqualGroup != -1) {
				// remove groups of optionals that
				$path =
					preg_replace("/".preg_quote("[".
							$finalGroupOfOptionals[$biggestEqualGroup]
						."]", "/")."/", "", $path);
			}
			$path = preg_replace("/\[|\]/", "", $path);
		/*} else {
			// else removes everything that is inside of brackets
			$path = preg_replace("/\[.*\]/", "", $path);
		}*/
		
		// will add get variables (this variables are the remain ones from $variables that were not set in $path already)
		// http_build_query construct a url based in $variables passed to it
		// preg_replace replaces the escaped brackets with an index and real brackets without index
		if (count($variables) > 0)
			$path .= "?" . preg_replace('/\%5B\d+\%5D/', '[]', http_build_query($variables));
		
		// if there are some more variables to substitute, it will raise a exception
		preg_match("/\{.*\}/", $path, $matches);
		if (isset($matches[0])) {
			throw new PlatformException("There are some variables that weren't replaced.",
				PlatformException::ERROR_NOT_FOUND_DANGER);
		}
		
		if ($addSchemeAndHost) $path = Configurations::getSchemeAndHost() . $path;
		
		return $path;
	}
	
	/**
	 * @param string $url
	 * @return string[] - array with all group of optionals order by the biggest to the smallest
	 * 					   - first is the main one, the others are all inside of the previous
	 */
	private static function getGroupOfOptionalsInUrl(string $url): array
	{
		if (preg_match("/\[(.*)\]/", $url, $matches)) {
			$groupOfOptionals = self::getGroupOfOptionalsInUrl($matches[1]);
			if ($groupOfOptionals === [])
				return [$matches[1]];
			else {
				return array_merge([$matches[1]], $groupOfOptionals);
			}
		} else {
			return [];
		}
		
		// /{urlCode}[/{orderType}[/{menuId}[/{productId}]]]
		// /{orderType}[/{menuId}[/{productId}]]
		// /{menuId}[/{productId}]
		// /{productId}
	}
	
	/**
	 * @param string $date
	 * @param string $fromTimezone
	 * @param string $toTimezone
	 * @param string $format
	 * @return string
	 */
	public static function changeTimezoneDate(string $date, string $fromTimezone = "UTC", string $toTimezone = "UTC",
											  string $format = 'Y-m-d H:i:s'): string
	{
		$dateTime = new \DateTime($date, new \DateTimeZone($fromTimezone));
		if ($fromTimezone != $toTimezone)
			$dateTime->setTimezone(new \DateTimeZone($toTimezone));
		return $dateTime->format($format);
	}
	
	/**
	 * Returns a valid date in the format expected
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
	 * @throws \Exception
	 */
	public static function returnValidDate(string $date = "now", string $format = 'Y-m-d H:i:s'): string
	{
		// self::changeTimezoneDate already returns a validDate
		// if fromTimezone and toTimezone are the same, a validDate will be returned without changing timezone
		return self::changeTimezoneDate($date, "UTC", "UTC", $format);
	}
	
	/**
	 * Get $date1 - $date2 in $diffUnit
	 *
	 * @param string $date1
	 * @param string $date2
	 * @param string $diffUnit - "years", "months", "days", "hours", "minutes", "seconds"
	 * @return int
	 * @throws PlatformException
	 *
	 * @note: Between $date1 = "2020-09-03 12:00:00" and $date2 = "2020-09-04 11:00:00" with $diffUnit = "days"
	 *        will return 0 days. If you want that a method call with such parameters return 1 day,
	 *        please use self::getDifferenceBetweenUnitTime()
	 */
	public static function getDifferenceBetweenDates(string $date1, string $date2, string $diffUnit = 'days'): int
	{
		$diffUnitEqualOrBelowDays = false;
		
		$yearMultiplier = 0;
		$monthMultiplier = 0;
		$dayMultiplier = 0;
		$hourMultiplier = 0;
		$minuteMultiplier = 0;
		$secondMultiplier = 0;
		switch ($diffUnit) {
			case 'years':
				$yearMultiplier = 1;
				break;
			case 'months':
				$yearMultiplier = 12;
				$monthMultiplier = 1;
				break;
			case 'days':
				$dayMultiplier = 1;
				$diffUnitEqualOrBelowDays = true;
				break;
			case 'hours':
				$hourMultiplier = 1;
				$dayMultiplier = 24;
				$diffUnitEqualOrBelowDays = true;
				break;
			case 'minutes':
				$minuteMultiplier = 1;
				$hourMultiplier = 60;
				$dayMultiplier = 24 * 60;
				$diffUnitEqualOrBelowDays = true;
				break;
			case 'seconds':
				$secondMultiplier = 1;
				$minuteMultiplier = 60;
				$hourMultiplier = 24 * 60;
				$dayMultiplier = 24 * 60 * 60;
				$diffUnitEqualOrBelowDays = true;
				break;
			default:
				throw new PlatformException('formatDiff parameter is not possible. ' .
					'Please, see which strings you can use as formatDiff parameter.',
					PlatformException::ERROR_PARAMETER_NOT_VALID);
		}
		
		$date1 = (new \DateTime($date1));
		$date2 = new \DateTime($date2);
		$diff = $date2->diff($date1);
		
		if ($diffUnitEqualOrBelowDays)
			return ($diff->invert ? -1 : 1) * ($diff->days * $dayMultiplier + $diff->h * $hourMultiplier +
					$diff->i * $minuteMultiplier + $diff->s * $secondMultiplier);
		else
			return ($diff->invert ? -1 : 1) * ($diff->y * $yearMultiplier + $diff->m * $monthMultiplier);
	}
	
	/**
	 * Get $date1 - $date2 in $diffUnit
	 *
	 * @param string $date1
	 * @param string $date2
	 * @param string $diffUnit
	 * @return int
	 * @throws PlatformException
	 *
	 * @note: Between $date1 = "2020-09-03 12:00:00" and $date2 = "2020-09-04 11:00:00" with $diffUnit = "days"
	 *        will return 1 day. If you want that a method call with such parameters return 0 days,
	 *        please use self::getDifferenceBetweenDates()
	 */
	public static function getDifferenceBetweenUnitTimes(string $date1, string $date2, string $diffUnit = 'days'): int
	{
		switch ($diffUnit) {
			case 'years':
				return (int)(new \DateTime($date1))->format('Y') - (int)(new \DateTime($date2))->format('Y');
			case 'months':
				return self::getDifferenceBetweenUnitTimes($date1, $date2, 'years') * 12 +
					((int)(new \DateTime($date1))->format('n') - (int)(new \DateTime($date2))->format('n'));
			case 'days':
				$date1 = new \DateTime((new \DateTime($date1))->format('Y-m-d'));
				$date2 = new \DateTime((new \DateTime($date2))->format('Y-m-d'));
				return (int)$date2->diff($date1)->format('%r%a');
			case 'hours':
				return self::getDifferenceBetweenUnitTimes($date1, $date2, 'days') * 24 +
					((int)(new \DateTime($date1))->format('G') - (int)(new \DateTime($date2))->format('G'));
			case 'minutes':
				return self::getDifferenceBetweenUnitTimes($date1, $date2, 'hours') * 60 +
					((int)(new \DateTime($date1))->format('i') - (int)(new \DateTime($date2))->format('i'));
			case 'seconds':
				return self::getDifferenceBetweenUnitTimes($date1, $date2, 'minutes') * 60 +
					((int)(new \DateTime($date1))->format('s') - (int)(new \DateTime($date2))->format('s'));
			default:
				throw new PlatformException('formatDiff parameter is not possible. ' .
					'Please, see which strings you can use as formatDiff parameter.',
					PlatformException::ERROR_PARAMETER_NOT_VALID);
		}
	}
    
    /**
     * @param $string
     * @return string
     */
    public static function removeAccents($string): string
    {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;
        
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );
        
        $string = strtr($string, $chars);
        return $string;
    }
}