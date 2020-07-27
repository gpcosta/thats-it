<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 17:39
 */

namespace ThatsIt\Response;

/**
 * Inspired heavily in patricklouys/http (https://github.com/PatrickLouys/http)
 * See: https://github.com/PatrickLouys/http/blob/master/src/HttpResponse.php
 * Many functions were copy-pasted with little corrections
 *
 * Class HttpResponse
 * @package ThatsIt\Response
 */
abstract class HttpResponse
{
    /**
     * @var string
     */
    protected $version = '1.1';
    
    /**
     * @var int
     */
    protected $statusCode = 200;
    
    /**
     * @var string
     */
    protected $statusText = 'OK';
    
    /**
     * @var array[name => array[value1, value2, ...]]
     */
    protected $headers = [];
    
    /**
     * @var array[name => value]
     */
    protected $cookies = [];
    
    /**
     * @var array[name => value]
     */
    protected $variables = [];
    
    /**
     * @var string
     */
    protected $environment = 'production';
    
    /**
     * @var array
     */
    private $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];
    
    /**
     * Returns the body content.
     *
     * @return string
     */
    abstract public function getContent(): string;
    
    /**
     * Sets the HTTP status code.
     *
     * @param int $statusCode
     * @param string|null $statusText
     */
    public function setStatusCode(int $statusCode, string $statusText = null): void
    {
        if ($statusText === null
            && array_key_exists((int) $statusCode, $this->statusTexts)
        ) {
            $statusText = $this->statusTexts[$statusCode];
        }
        
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
    }
    
    /**
     * Returns the HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * Adds a header with the given name.
     *
     * @param string $name
     * @param $value
     */
    public function addHeader(string $name, $value): void
    {
        $this->headers[$name][] = (string) $value;
    }
    
    /**
     * Sets a new header for the given name.
     *
     * Replaces all headers with the same names.
     *
     * @param string $name
     * @param $value
     */
    public function setHeader(string $name, $value): void
    {
        $this->headers[$name] = [
            (string) $value,
        ];
    }
    
    /**
     * Returns an array with the HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = array_merge(
            $this->getCookieHeaders(),
            $this->getRequestLineHeaders(),
            $this->getStandardHeaders()
        );
        
        return $headers;
    }
    
    /**
     * @param string $name
     * @return null|Cookie
     */
    public function getCookie(string $name): ?Cookie
    {
        if (array_key_exists($name, $this->cookies))
            return $this->cookies[$name];
        
        return null;
    }
    
    /**
     * Adds a new cookie.
     *
     * @param Cookie $cookie
     */
    public function addCookie(Cookie $cookie): void
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }
    
    /**
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }
    
    /**
     * @param array $cookies
     */
    public function setCookies(array $cookies): void
    {
        $this->cookies = $cookies;
    }
    
    /**
     * Deletes a cookie.
     *
     * @param Cookie $cookie
     */
    public function deleteCookie(Cookie $cookie): void
    {
        $cookie->setValue('');
        $cookie->setMaxAge(-1);
        $this->cookies[$cookie->getName()] = $cookie;
    }
    
    /**
     * @param string $name
     * @param $value
     */
    public function addVariable(string $name, $value): void
    {
        $this->variables[$name] = $value;
    }
    
    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getVariable(string $name, $default = null)
    {
        if (array_key_exists($name, $this->variables))
            return $this->variables[$name];
        return $default;
    }
    
    /**
     * @param array $variables
     */
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
    
    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }
    
    /**
     * @param string $environment
     */
    public function setEnvironment(string $environment): void
    {
        if ($environment === 'production' || $environment === 'development')
            $this->environment = $environment;
    }
    
    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
    
    /**
     * @return array[string]
     */
    private function getRequestLineHeaders(): array
    {
        $headers = [];
        
        $requestLine = sprintf(
            'HTTP/%s %s %s',
            $this->version,
            $this->statusCode,
            $this->statusText
        );
        
        $headers[] = trim($requestLine);
        
        return $headers;
    }
    
    /**
     * @return array[string]
     */
    private function getStandardHeaders(): array
    {
        $headers = [];
        
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }
        
        return $headers;
    }
    
    /**
     * @return array[string]
     */
    private function getCookieHeaders(): array
    {
        $headers = [];
        
        foreach ($this->cookies as $cookie) {
            $headers[] = 'Set-Cookie: ' . $cookie->getHeaderString();
        }
        
        return $headers;
    }
}
