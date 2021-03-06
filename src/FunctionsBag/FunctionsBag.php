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
	 * @param string $name
	 * @param array $variables [name => value]
	 * @param bool $withOptional (url with optional part or not. when there is no optional part, doesn't matter its value)
	 * @param bool $addDomain (if true, will add the domain to the url)
	 * @return string
	 * @throws PlatformException
	 */
	public static function getUrl(string $name, array $variables = [], bool $withOptional = false,
								  bool $addDomain = false): string
	{
		static $routes;
		
		// just to load routes once
		if (!$routes) $routes = Configurations::getRoutesConfig(self::$routesPath);
		
		if (!isset($routes[$name])) {
			throw new PlatformException("There are no url for '" . $name . "'",
				PlatformException::ERROR_NOT_FOUND_DANGER);
		}
		
		$path = $routes[$name]['path'];
		
		if ($withOptional) {
			// obtain all optional groups, even the ones inside other optinal groups
			// ex: /{urlCode}[/{orderType}[/{menuId}[/{productId}]]]
			$initialGroupOfOptionals = self::getGroupOfOptionalsInUrl($path);
		}
		
		// save variables names that were already used in url
		$alreadyUsedVariablesInPath = [];
		
		// will substitute all variables for their value
		foreach ($variables as $name => $value) {
			// arrays are only used as GET (or query) parameters
			if (is_array($value))
				continue;
			
			if ($value) {
				// limit: -1 is the same as no limit; $count will know how many replaces happened
				$path = preg_replace("/\{" . $name . "(\:.*){0,1}\}/U", $value, $path, -1, $count);
				// if any replace happened, this variable is already used
				// if not, this variable should be added to path as a GET parameter
				if ($count) $alreadyUsedVariablesInPath[] = $name;
			}
		}
		
		// remove all variables already set in url (this step was not made in last foreach just as a safety measure)
		foreach ($alreadyUsedVariablesInPath as $variableName) unset($variables[$variableName]);
		
		if ($withOptional) {
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
		} else {
			// else removes everything that is inside of brackets
			$path = preg_replace("/\[.*\]/", "", $path);
		}
		
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
		
		if ($addDomain) $path = Configurations::getDomain() . $path;
		
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
}