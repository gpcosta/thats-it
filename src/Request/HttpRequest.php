<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 09:44
 */

namespace ThatsIt\Request;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\ClientException;
use ThatsIt\Request\Exception\MissingRequestMetaVariableException;
use ThatsIt\Sanitizer\Sanitizer;

/**
 * Inspired heavily in patricklouys/http (https://github.com/PatrickLouys/http)
 * See: https://github.com/PatrickLouys/http/blob/master/src/HttpRequest.php
 * Many functions were copy-pasted with little corrections
 *
 * Class HttpRequest
 * @package ThatsIt\Request
 */
class HttpRequest
{
	/**
	 * @var Route
	 */
	protected $currentRoute;
	
	/**
	 * @var string
	 */
	protected $method;
	
	/**
	 * @var array
	 */
	protected $pathParameters;
	
	/**
	 * @var array
	 */
	protected $queryParameters;
	
	/**
	 * @var array
	 */
	protected $bodyParameters;
	
	/**
	 * @var array
	 */
	protected $server;
	
	/**
	 * @var array
	 */
	protected $files;
	
	/**
	 * @var array
	 */
	protected $cookies;
	
	/**
	 * @var string
	 */
	protected $inputStream;
	
	/**
	 * HttpRequest constructor.
	 * @param array $get
	 * @param array $post
	 * @param array $cookies
	 * @param array $files
	 * @param array $server
	 * @param string $inputStream
	 * @throws MissingRequestMetaVariableException
	 * @throws \ThatsIt\Exception\PlatformException
	 */
	public function __construct(array $get, array $post, array $cookies, array $files, array $server,
								 string $inputStream = '')
	{
		$this->cookies = $cookies;
		$this->files = [];
		foreach ($files as $key => $file) {
			// error == 4 means that there is no uploaded file
			if (is_array($file) && $file['error'] != 4)
				$this->files[$key] = $file;
		}
		$this->server = $server;
		$this->inputStream = $inputStream;
		
		// define current method
		$this->method = $this->getServerVariable('REQUEST_METHOD');
		if (isset($get['_method']))
			$this->method = $get['_method'];
		if (isset($post['_method']))
			$this->method = $post['_method'];
		
		$routeAndVars = $this->getChosenRouteAndVars(Configurations::getRoutesConfig());
		$this->currentRoute = $routeAndVars['route'];
		
		$this->pathParameters = $this->getSanitizedParameters($routeAndVars['vars'], $this->currentRoute);
		$this->queryParameters = $this->getSanitizedParameters($get, $this->currentRoute);
		if ($this->getServerVariable('CONTENT_TYPE', '') == 'application/json' ||
				$this->getServerVariable('HTTP_CONTENT_TYPE', '') == 'application/json')
			$this->bodyParameters = $this->getSanitizedParameters(json_decode($inputStream), $this->currentRoute);
		else
			$this->bodyParameters = $this->getSanitizedParameters($post, $this->currentRoute);
	}
	
	/**
	 * @param array $routes
	 * @return array['route', 'vars' => [...]]
	 * @throws \Exception
	 */
	private function getChosenRouteAndVars(array $routes): array
	{
		$toReturn = ['route' => null, 'vars' => array()];
		$path = $this->getPath();
		if ($path != '/' && mb_substr($path, -1) == '/')
			$path = mb_substr($path, 0, -1);
		$routeInfo = $this->getDispatcher($routes)->dispatch($this->method, $path);
		switch ($routeInfo[0]) {
			case Dispatcher::NOT_FOUND:
				throw new ClientException("Not found what was requested.", 404);
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = $routeInfo[1];
				throw new ClientException("The http method used is not valid. The only valid ones are " .
					implode(", ", $allowedMethods) . ".", 405);
				break;
			case Dispatcher::FOUND:
				$toReturn['route'] = $routeInfo[1];
				$toReturn['vars'] = $routeInfo[2];
				break;
		}
		
		return $toReturn;
	}
	
	/**
	 * @param array $routes
	 * @return Dispatcher
	 */
	private function getDispatcher(array $routes): Dispatcher
	{
		return simpleDispatcher(function (RouteCollector $r) use ($routes) {
			foreach ($routes as $routeName => $route) {
				$handler = new Route($routeName, $route);
				$r->addRoute($route['httpMethods'], $route['path'], $handler);
			}
		});
	}
	
	/**
	 * Sanitize $givenParameters with the provided sanitizer
	 * methods provided by $currentRouteParameters
	 *
	 * @param array $givenParameters
	 * @param Route $currentRoute
	 * @return array
	 */
	private function getSanitizedParameters(array $givenParameters, Route $currentRoute): array
	{
		foreach ($givenParameters as $parameterName => $parameterValue) {
			$parameter = $currentRoute->getParameter($parameterName);
			$sanitizer = (is_array($parameter) && isset($parameter['sanitizer']) ?
				$parameter['sanitizer'] :
				Sanitizer::SANITIZER_NONE
			);
			$givenParameters[$parameterName] = Sanitizer::sanitize($parameterValue, $sanitizer);
		}
		return $givenParameters;
	}
	
	/**
	 * @return Route
	 */
	public function getCurrentRoute(): Route
	{
		return $this->currentRoute;
	}
	
	/**
	 * Returns all parameters.
	 *
	 * @return array
	 */
	public function getAllParameters(): array
	{
		return array_merge($this->pathParameters, $this->queryParameters, $this->bodyParameters, $this->files);
	}
	
	/**
	 * Returns a parameter value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getParameter($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->bodyParameters))
			return $this->bodyParameters[$key];
		if (array_key_exists($key, $this->queryParameters))
			return $this->queryParameters[$key];
		if (array_key_exists($key, $this->pathParameters))
			return $this->pathParameters[$key];
		if (array_key_exists($key, $this->files))
			return $this->files[$key];
		return $defaultValue;
	}
	
	/**
	 * Returns all path parameters (params in path - before ?).
	 *
	 * @return array
	 */
	public function getPathParameters(): array
	{
		return $this->pathParameters;
	}
	
	/**
	 * Returns a path parameter value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getPathParameter($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->pathParameters))
			return $this->pathParameters[$key];
		return $defaultValue;
	}
	
	/**
	 * Returns all query parameters (params after ?).
	 *
	 * @return array
	 */
	public function getQueryParameters(): array
	{
		return $this->queryParameters;
	}
	
	/**
	 * Returns a query parameter value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getQueryParameter($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->queryParameters))
			return $this->queryParameters[$key];
		return $defaultValue;
	}
	
	/**
	 * Returns all body (post or json) parameters.
	 *
	 * @return array
	 */
	public function getBodyParameters(): array
	{
		return $this->bodyParameters;
	}
	
	/**
	 * Returns a body parameter value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getBodyParameter($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->bodyParameters))
			return $this->bodyParameters[$key];
		return $defaultValue;
	}
	
	/**
	 * Returns a File Iterator.
	 *
	 * @return array
	 */
	public function getFiles(): array
	{
		return $this->files;
	}
	
	/**
	 * Returns a file value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getFile($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->files)) {
			return $this->files[$key];
		}
		return $defaultValue;
	}
	
	/**
	 * Returns a file value or a default value if none is set.
	 *
	 * @param $key
	 * @return UploadedFile|null
	 */
	public function getFileAsFile($key): ?UploadedFile
	{
		$file = $this->getFile($key, null);
		if ($file === null)
			return null;
		
		return new UploadedFile($file);
	}
	
	/**
	 * Returns a Cookie Iterator.
	 *
	 * @return array
	 */
	public function getCookies(): array
	{
		return $this->cookies;
	}
	
	/**
	 * Returns a cookie value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getCookie($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->cookies)) {
			return $this->cookies[$key];
		}
		return $defaultValue;
	}
	
	/**
	 * Returns raw values from the read-only stream that allows you to read raw data from the request body.
	 *
	 * @return string
	 */
	public function getRawBody(): string
	{
		return $this->inputStream;
	}
	
	/**
	 * The URI which was given in order to access this page
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getUri(): string
	{
		return $this->getServerVariable('REQUEST_URI');
	}
	
	/**
	 * Return just the path
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getPath(): string
	{
		return strtok($this->getServerVariable('REQUEST_URI'), '?');
	}
	
	/**
	 * Which request method was used to access the page;
	 * i.e. 'GET', 'POST', 'PUT', 'DELETE'.
	 *
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}
	
	/**
	 * Return the current host that comes in $_SERVER variable
	 *
	 * @param bool $withHttp (string return will have the "prefix" http or https)
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getHost(bool $withHttp = true): string
	{
		$host = $this->getServerVariable('HTTP_HOST');
		if ($withHttp)
			$host = $this->getHttpOrHttps() . "://" . $host;
		return $host;
	}
	
	/**
	 * @return string
	 */
	public function getHttpOrHttps()
	{
		// If the request was sent with HTTPS you will have a extra parameter in the $_SERVER superglobal - $_SERVER['HTTPS']
		$httpOrHttps = "http";
		if ($this->isSecure()) $httpOrHttps .= "s";
		return $httpOrHttps;
	}
	
	/**
	 * Contents of the Accept: header from the current request, if there is one.
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getHttpAccept(): string
	{
		return $this->getServerVariable('HTTP_ACCEPT');
	}
	
	/**
	 * The address of the page (if any) which referred the user agent to the
	 * current page.
	 *
	 * @return string - if there is any referer, it is returned an empty string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getReferer(): string
	{
		return $this->getServerVariable('HTTP_REFERER', '');
	}
	
	/**
	 * Content of the User-Agent header from the request, if there is one.
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getUserAgent(): string
	{
		return $this->getServerVariable('HTTP_USER_AGENT');
	}
	
	/**
	 * The IP address from which the user is viewing the current page.
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getIpAddress(): string
	{
		return $this->getServerVariable('REMOTE_ADDR');
	}
	
    /**
     * @return array
     */
    public function getInfoBasedOnIp(): array
    {
        static $ipInfo;
        if ($ipInfo !== null)
            return $ipInfo;
        
        $response = (new CurlRequest('https://api.freegeoip.app/json/'.$this->getIpAddress().
            '?apikey='.Configurations::getFieldFromConfig('freegeoipApiKey'),
            'GET'))
            ->send();
        $response = json_decode($response, true);
        if (!is_array($response))
            $response = [];
        $ipInfo = [];
        $ipInfo['ip'] = $this->getIpAddress();
        $ipInfo['country'] = isset($response['country_code']) ? $response['country_code'] : '';
        $ipInfo['region'] = isset($response['region_name']) ? $response['region_name'] : '';
        $ipInfo['city'] = isset($response['city']) ? $response['city'] : '';
        $ipInfo['timezone'] = isset($response['time_zone']) ? $response['time_zone'] : '';
        return $ipInfo;
    }
	
	/**
	 * Obtain the several suggested languages by the browser for the current user
	 * All suggested languages have a suitability coefficient (q) for the current user
	 * When q coefficent is not present, q = 1
	 * Example of the format: en-ca,en;q=0.8,en-us;q=0.6,de-de;q=0.4,de;q=0.2
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getBrowserLanguage(): string
	{
		return $this->getServerVariable('HTTP_ACCEPT_LANGUAGE');
	}
	
	/**
	 * Checks to see whether the current request is using HTTPS.
	 *
	 * @return bool
	 */
	public function isSecure(): bool
	{
		return (array_key_exists('HTTPS', $this->server)
			&& $this->server['HTTPS'] !== 'off'
		);
	}
	
	/**
	 * The query string, if any, via which the page was accessed.
	 *
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getQueryString()
	{
		return $this->getServerVariable('QUERY_STRING');
	}
	
	/**
	 * @param $key
	 * @param $defaultValue - if null, a MissingRequestMetaVariableException is raised
	 * 						   if not found such server variable
	 * @return mixed
	 * @throws MissingRequestMetaVariableException
	 */
	public function getServerVariable($key, $defaultValue = null)
	{
		if (!array_key_exists($key, $this->server)) {
			if ($defaultValue !== null)
				return $defaultValue;
			else
				throw new MissingRequestMetaVariableException($key);
		}
		return $this->server[$key];
	}
}