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
	 * @var array
	 */
	protected $currentRoute;
	
	/**
	 * @var array
	 */
	protected $getParameters;
	
	/**
	 * @var array
	 */
	protected $postParameters;
	
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
	 */
	public function __construct(array $get, array $post, array $cookies, array $files, array $server,
								 string $inputStream = '')
	{
		$this->getParameters = $this->getSanitizedParameters($get);
		$this->postParameters = $this->getSanitizedParameters($post);
		$this->cookies = $cookies;
		$this->files = [];
		foreach ($files as $key => $file) {
			// error == 4 means that there is no uploaded file
			if (is_array($file) && $file['error'] != 4)
				$this->files[$key] = $file;
		}
		$this->server = $server;
		$this->inputStream = $inputStream;
		
		$routeAndVars = $this->getChosenRouteAndVars(Configurations::getRoutesConfig());
		$this->currentRoute = $routeAndVars['route'];
		$this->getParameters = array_merge($this->getParameters, $routeAndVars['vars']);
	}
	
	/**
	 * @param array $routes
	 * @return array['route', 'vars' => [...]]
	 * @throws \Exception
	 */
	private function getChosenRouteAndVars(array $routes): array
	{
		$toReturn = ['route' => null, 'vars' => array()];
		$routeInfo = $this->getDispatcher($routes)->dispatch($this->getMethod(), $this->getPath());
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
	 * @param array $currentRouteParameters
	 * @return array
	 */
	private function getSanitizedParameters(array $givenParameters): array
	{
		foreach ($givenParameters as $parameterName => $parameterValue) {
			$sanitizer = (isset($currentRouteParameters[$parameterName]['sanitizer']) ?
				$currentRouteParameters[$parameterName]['sanitizer'] : Sanitizer::SANITIZER_NONE
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
	 * Returns a parameter value or a default value if none is set.
	 *
	 * @param $key
	 * @param null $defaultValue
	 * @return mixed|null
	 */
	public function getParameter($key, $defaultValue = null)
	{
		if (array_key_exists($key, $this->postParameters)) {
			return $this->postParameters[$key];
		}
		if (array_key_exists($key, $this->getParameters)) {
			return $this->getParameters[$key];
		}
		return $defaultValue;
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
		if (array_key_exists($key, $this->getParameters)) {
			return $this->getParameters[$key];
		}
		return $defaultValue;
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
		if (array_key_exists($key, $this->postParameters)) {
			return $this->postParameters[$key];
		}
		return $defaultValue;
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
	 * Returns all parameters.
	 *
	 * @return array
	 */
	public function getParameters(): array
	{
		return array_merge(
			$this->getParameters, $this->postParameters, $this->files
		);
	}
	
	/**
	 * Returns all query (get) parameters.
	 *
	 * @return array
	 */
	public function getQueryParameters(): array
	{
		return $this->getParameters;
	}
	
	/**
	 * Returns all body (post) parameters.
	 *
	 * @return array
	 */
	public function getBodyParameters(): array
	{
		return $this->postParameters;
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
	 * Returns a Cookie Iterator.
	 *
	 * @return array
	 */
	public function getCookies(): array
	{
		return $this->cookies;
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
	 * @throws MissingRequestMetaVariableException
	 */
	public function getMethod(): string
	{
		$method = $this->getServerVariable('REQUEST_METHOD');
		if ($this->getParameter("_method")) {
			$method = strtoupper($this->getParameter("_method"));
		}
		return $method;
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
	 * @return string
	 * @throws MissingRequestMetaVariableException
	 */
	public function getReferer(): string
	{
		return $this->getServerVariable('HTTP_REFERER');
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
	 * @return mixed
	 * @throws MissingRequestMetaVariableException
	 */
	private function getServerVariable($key)
	{
		if (!array_key_exists($key, $this->server)) {
			throw new MissingRequestMetaVariableException($key);
		}
		return $this->server[$key];
	}
}