<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 09:44
 */

namespace ThatsIt\Request;

use ThatsIt\Request\Exception\MissingRequestMetaVariableException;

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
    protected $getParameters;
    
    /**
     * @var array
     */
    protected $postParameters;
    
    /**
     * @var array
     */
    protected $putParameters;
    
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
    
    public function __construct(
        array $get,
        array $post,
        array $cookies,
        array $files,
        array $server,
        string $inputStream = ''
    ) {
        $this->getParameters = $get;
        $this->postParameters = $post;
        parse_str($inputStream, $this->putParameters);
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->inputStream = $inputStream;
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
        if (array_key_exists($key, $this->putParameters)) {
            return $this->putParameters[$key];
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
     * Returns a put parameter value or a default value if none is set.
     *
     * @param $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getPutParameter($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->putParameters)) {
            return $this->putParameters[$key];
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
            $this->getParameters, $this->postParameters, $this->putParameters
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
     * Returns all put parameters.
     *
     * @return array
     */
    public function getPutParameters(): array
    {
        return $this->putParameters;
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
     * @return mixed
     * @throws MissingRequestMetaVariableException
     */
    public function getUri()
    {
        return $this->getServerVariable('REQUEST_URI');
    }
    
    /**
     * Return just the path
     *
     * @return string
     * @throws MissingRequestMetaVariableException
     */
    public function getPath()
    {
        return strtok($this->getServerVariable('REQUEST_URI'), '?');
    }
    
    /**
     * Which request method was used to access the page;
     * i.e. 'GET', 'POST', 'PUT', 'DELETE'.
     *
     * @return mixed
     * @throws MissingRequestMetaVariableException
     */
    public function getMethod()
    {
        $method = $this->getServerVariable('REQUEST_METHOD');
        if ($this->getParameter("_method")) {
            $method = strtoupper($this->getParameter("_method"));
        }
        return $method;
    }
    
    /**
     * Contents of the Accept: header from the current request, if there is one.
     *
     * @return string
     * @throws MissingRequestMetaVariableException
     */
    public function getHttpAccept()
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
    public function getReferer()
    {
        return $this->getServerVariable('HTTP_REFERER');
    }
    
    /**
     * Content of the User-Agent header from the request, if there is one.
     *
     * @return string
     * @throws MissingRequestMetaVariableException
     */
    public function getUserAgent()
    {
        return $this->getServerVariable('HTTP_USER_AGENT');
    }
    
    /**
     * The IP address from which the user is viewing the current page.
     *
     * @return string
     * @throws MissingRequestMetaVariableException
     */
    public function getIpAddress()
    {
        return $this->getServerVariable('REMOTE_ADDR');
    }
    
    /**
     * Checks to see whether the current request is using HTTPS.
     *
     * @return boolean
     */
    public function isSecure()
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