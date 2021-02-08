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
	 * @var string
	 */
	private $method;
	
	/**
	 * @var string
	 */
	private $postFields;
	
	/**
	 * @var int
	 */
	private $postFieldsFormat;
    
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
     * @var array
     */
    private $httpHeaders;
    
    /**
     * @var string
	 *
	 * if you want to use this class for a web crawler, use this user agent:
	 * 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36'
	 *
     * If anything goes wrong with the previous user agent, try this one
     * 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0'
     */
    private $userAgent;
    
    /**
     * @var string
     */
    private $filenameForCookies;
    
    /**
     * CurlRequest constructor.
     * @param string $url
     * @param string $encoding
     * @param string $method
     * @param array $postFields
     * @param int $postFieldsFormat (can get any constant that starts with CURL_POST_FIELDS_TO_*)
     * @param int $port
     * @param array $httpHeader
     * @param int $maxRedirections
     * @param int $timeout
     * @param string $httpVersion
     * @param string $userAgent
     * @param string $filenameForCookies
     * @throws PlatformException
     */
    public function __construct(string $url, string $method)
    {
		if (!in_array($method, array("GET", "POST", "PUT", "DELETE"))) {
			throw new PlatformException("Only GET, POST, PUT and DELETE are allowed",
				PlatformException::ERROR_NOT_FOUND_SOFT);
		}
		
		$this->url = $url;
		$this->method = $method;
		$this->postFields = [];
		$this->postFieldsFormat = self::CURL_POST_FIELDS_TO_QUERY;
		$this->httpVersion = "CURL_HTTP_VERSION_1_1";
	
		/*$this->encoding = "";
		$this->port = 80;
		$this->maxRedirections = 10;
		$this->timeout = 30;
		$this->httpHeaders = [
			"Content-Type: application/x-www-form-urlencoded; charset=utf-8",
			"cache-control: no-cache"
		];
		$this->userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) ".
			"AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36";*/
    }
	
	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}
	
	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}
	
	/**
	 * @param bool $toFormat
	 * @return mixed
	 */
	public function getPostFields(bool $toFormat = false)
	{
		if (!$toFormat)
			return $this->postFields;
		
		switch ($this->postFieldsFormat) {
			case self::CURL_POST_FIELDS_TO_JSON:
				$postFields = json_encode($this->postFields, JSON_PRETTY_PRINT);
				break;
			// query string is the default (x-www-form-urlencoded)
			case self::CURL_POST_FIELDS_TO_QUERY:
			default:
				$postFields = http_build_query($this->postFields);
				break;
		}
		return $postFields;
	}
	
	/**
	 * @param string $postFields
	 * @return CurlRequest
	 */
	public function setPostFields(string $postFields): self
	{
		$this->postFields = $postFields;
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param $value
	 * @return CurlRequest
	 */
	public function addPostField(string $key, $value): self
	{
		$this->postFields[$key] = $value;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getPostFieldsFormat(): int
	{
		return $this->postFieldsFormat;
	}
	
	/**
	 * @param int $postFieldsFormat
	 * @return CurlRequest
	 */
	public function setPostFieldsFormat(int $postFieldsFormat): self
	{
		$this->postFieldsFormat = $postFieldsFormat;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getPort(): int
	{
		return $this->port;
	}
	
	/**
	 * @param int $port
	 * @return CurlRequest
	 */
	public function setPort(int $port): self
	{
		$this->port = $port;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getEncoding(): string
	{
		return $this->encoding;
	}
	
	/**
	 * @param string $encoding
	 * @return CurlRequest
	 */
	public function setEncoding(string $encoding): self
	{
		$this->encoding = $encoding;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getMaxRedirections(): int
	{
		return $this->maxRedirections;
	}
	
	/**
	 * @param int $maxRedirections
	 * @return CurlRequest
	 */
	public function setMaxRedirections(int $maxRedirections): self
	{
		$this->maxRedirections = $maxRedirections;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getTimeout(): int
	{
		return $this->timeout;
	}
	
	/**
	 * @param int $timeout
	 * @return CurlRequest
	 */
	public function setTimeout(int $timeout): self
	{
		$this->timeout = $timeout;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getHttpVersion(): string
	{
		return $this->httpVersion;
	}
	
	/**
	 * @param string $httpVersion
	 * @return CurlRequest
	 */
	public function setHttpVersion(string $httpVersion): self
	{
		$this->httpVersion = $httpVersion;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getHttpHeaders(bool $toFormat = false)
	{
		if (!$toFormat)
			return $this->httpHeaders;
		
		$httpHeader = [];
		foreach ($this->httpHeaders as $key => $value)
			$httpHeader[] = $key.': '.$value;
		return $httpHeader;
	}
	
	/**
	 * @param array $httpHeader
	 * @return CurlRequest
	 */
	public function setHttpHeaders(array $httpHeaders): self
	{
		$this->httpHeaders = $httpHeaders;
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param $value
	 * @return CurlRequest
	 */
	public function addHttpHeader(string $key, $value): self
	{
		$this->httpHeaders[$key] = $value;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getUserAgent(): string
	{
		return $this->userAgent;
	}
	
	/**
	 * @param string $userAgent
	 * @return CurlRequest
	 */
	public function setUserAgent(string $userAgent): self
	{
		$this->userAgent = $userAgent;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getFilenameForCookies(): string
	{
		return $this->filenameForCookies;
	}
	
	/**
	 * the content of file $filenameForCookies will be used as cookies
	 *
	 * @param string $filenameForCookies
	 * @return CurlRequest
	 */
	public function setFilenameForCookies(string $filenameForCookies): self
	{
		$this->filenameForCookies = $filenameForCookies;
		return $this;
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
		curl_setopt($curl, CURLOPT_HTTP_VERSION, $this->httpVersion);
		if (strtoupper($this->method) == 'POST')
			curl_setopt($curl, CURLOPT_POST, true);
		else
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getPostFields(true));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHttpHeaders(true));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		
		switch (parse_url($this->url, PHP_URL_HOST)) {
			case 'https':
				if (!$this->port)
					$this->port = 443;
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
				break;
			case 'http':
				if (!$this->port)
					$this->port = 80;
		}
		curl_setopt($curl, CURLOPT_PORT, $this->port);
		
		/*curl_setopt($curl, CURLOPT_ENCODING, $this->encoding);
		curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirections);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);*/
	
		// cookies
		if ($this->filenameForCookies) {
			if (!is_file($this->filenameForCookies))
				file_put_contents($this->filenameForCookies, "");
		
			// set where cookies will be stored
			curl_setopt($curl, CURLOPT_COOKIEJAR, $this->filenameForCookies);
			// from where it will get cookies
			curl_setopt($curl, CURLOPT_COOKIEFILE, $this->filenameForCookies);
		}
		
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
		
		if ($error)
			throw new PlatformException(
				"There was an error in CURL request. Error: ".$error,
				PlatformException::ERROR_CURL_REQUEST
			);
		return $response;
    }
}