<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 10:57
 */

namespace ThatsIt\Request;

use ThatsIt\Exception\PlatformException;

/**
 * Class CurlRequest
 * @package ThatsIt\Request
 */
class CurlRequest
{
    /**
     * @var string
     */
    private $url;
    
    /**
     * @var int
     */
    private $port;
    
    /**
     * @var string
     */
    private $encoding;
    
    /**
     * @var int
     */
    private $maxRedirections;
    
    /**
     * @var int
     */
    private $timeout;
    
    /**
     * @var string
     */
    private $httpVersion;
    
    /**
     * @var string
     */
    private $method;
    
    /**
     * @var string
     */
    private $postFields;
    
    /**
     * @var array
     */
    private $httpHeader;
    
    /**
     * @var string
     */
    private $userAgent;
    
    /**
     * CurlRequest constructor.
     * @param string $url
     * @param string $encoding
     * @param string $method
     * @param array $postFields
     * @param int $port
     * @param int $maxRedirections
     * @param int $timeout
     * @param string $httpVersion
     * @param array $httpHeader
     * @param string $userAgent
     * @throws PlatformException
     */
    public function __construct(string $url, string $encoding, string $method,
                                 array $postFields, int $port = 80, int $maxRedirections = 10, int $timeout = 30,
                                 string $httpVersion = "CURL_HTTP_VERSION_1_1",
                                 array $httpHeader = [
                                     "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
                                     "cache-control: no-cache"
                                 ], string $userAgent = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) ".
                                    "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36")
    {
        $this->url = $url;
        $this->port = $port;
        $this->encoding = $encoding;
        $this->maxRedirections = $maxRedirections;
        $this->timeout = $timeout;
        $this->httpVersion = $httpVersion;
        
        if (!in_array($method, array("GET", "POST", "PUT", "DELETE"))) {
            throw new PlatformException("Only GET, POST, PUT and DELETE are allowed",
                PlatformException::ERROR_NOT_FOUND_SOFT);
        }
        
        $this->method = $method;
        $this->postFields = http_build_query($postFields);
        $this->httpHeader = $httpHeader;
        $this->userAgent = $userAgent;
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    public function send(): string
    {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_PORT, $this->port);
        curl_setopt($curl, CURLOPT_ENCODING, $this->encoding);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirections);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, $this->httpVersion);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postFields);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->httpHeader);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            throw new \Exception("There was an error in a CURL request. Error: ".$error, 500);
        } else {
            return $response;
        }
    }
}