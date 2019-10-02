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
     * $postFields to query string through http_build_query()
     */
    const CURL_POST_FIELDS_TO_QUERY = 1;
    
    /**
     * $postFields to json through json_encode()
     */
    const CURL_POST_FIELDS_TO_JSON = 2;
    
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
     *
     * If anything goes wrong, experiment the following user agent
     * 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0'
     */
    private $userAgent;
    
    /**
     * CurlRequest constructor.
     * @param string $url
     * @param string $encoding
     * @param string $method
     * @param array $postFields
     * @param int $postFieldsType (can get any constant that starts with CURL_POST_FIELDS_TO_*)
     * @param array $httpHeader
     * @param int $port
     * @param int $maxRedirections
     * @param int $timeout
     * @param string $httpVersion
     * @param string $userAgent
     * @throws PlatformException
     */
    public function __construct(string $url, string $encoding, string $method,
                                array $postFields, int $postFieldsType = self::CURL_POST_FIELDS_TO_QUERY,
                                int $port = 80, array $httpHeader = [
                                    "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
                                    "cache-control: no-cache"
                                ], int $maxRedirections = 10, int $timeout = 30,
                                string $httpVersion = "CURL_HTTP_VERSION_1_1",
                                string $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) ".
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
        $this->postFields = $this->preparePostFields($postFields, $postFieldsType);
        $this->httpHeader = $httpHeader;
        $this->userAgent = $userAgent;
    }
    
    /**
     * @return string
     * @throws PlatformException
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
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        /* to allow https connections */
        /*curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,1);
        curl_setopt($curl, CURLOPT_CAINFO, __DIR__.'/cacert.pem');
        curl_setopt($curl, CURLOPT_SSLVERSION, 'all');
        curl_setopt($curl, CURLOPT_CAPATH,__DIR__.'cacert.pem');*/
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
    
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($error) {
            throw new PlatformException(
                "There was an error in CURL request. Error: ".$error,
                PlatformException::ERROR_CURL_REQUEST
            );
        } else if ($httpCode >= 400) {
            throw new PlatformException(
                "The CURL request was not successful. Status code: ".$httpCode,
                PlatformException::ERROR_CURL_REQUEST
            );
        }
        
        return $response;
    }
    
    /**
     * Prepare $postFields to the requested format
     *
     * @param $postFields
     * @param $toFormat
     * @return string
     */
    private function preparePostFields($postFields, $toFormat): string
    {
        switch ($toFormat) {
            case self::CURL_POST_FIELDS_TO_JSON:
                $postFields = json_encode($postFields, JSON_PRETTY_PRINT);
                break;
            // query string is the default (x-www-form-urlencoded)
            case self::CURL_POST_FIELDS_TO_QUERY:
            default:
                $postFields = http_build_query($postFields);
                break;
        }
        return $postFields;
    }
}