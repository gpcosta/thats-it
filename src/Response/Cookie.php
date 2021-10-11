<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 17:43
 */

namespace ThatsIt\Response;

/**
 * Inspired heavily in patricklouys/http (https://github.com/PatrickLouys/http)
 * See: https://github.com/PatrickLouys/http/blob/master/src/HttpCookie.php
 * Many functions were copy-pasted with little corrections
 *
 * Class Cookie
 * @package ThatsIt\Response
 */
class Cookie
{
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $value;
    
    /**
     * @var string
     */
    private $domain;
    
    /**
     * @var string
     */
    private $path;
    
    /**
     * @var int (seconds until the cookie expire)
     */
    private $maxAge;
    
    /**
     * @var bool
     */
    private $secure;
    
    /**
     * @var bool
     */
    private $httpOnly;
    
    /**
     * @var string
     */
    private $sameSite;
    
    /**
     * Cookie constructor.
     * @param string $name
     * @param string $value
     * @param int $maxAge
     * @param bool $httpOnly
     * @param bool $secure
     * @param string $path
     * @param string $domain
     * @param string $sameSite
     */
    public function __construct(string $name, $value = '', int $maxAge = 0, bool $httpOnly = true,
                                bool $secure = true, $path = '/', string $domain = '', string $sameSite = 'Lax')
    {
        $this->name = $name;
        $this->value = (string) $value;
        $this->maxAge = $maxAge;
        $this->path = $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->domain = $domain;
        $this->sameSite = $sameSite;
    }
    
    /**
     * Returns the cookie name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the cookie max age in seconds.
     *
     * @param int $seconds
     */
    public function setMaxAge(int $seconds): void
    {
        $this->maxAge = $seconds;
    }
    
    /**
     * Sets the cookie value.
     *
     * @param  string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = (string) $value;
    }
    
    /**
     * Sets the cookie domain.
     *
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }
    
    /**
     * Sets the cookie path.
     *
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = (string) $path;
    }
    
    /**
     * Sets the cookie secure flag.
     *
     * @param bool $secure
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }
    
    /**
     * Sets the cookie httpOnly flag.
     *
     * @param bool $httpOnly
     */
    public function setHttpOnly(bool $httpOnly)
    {
        $this->httpOnly = $httpOnly;
    }
    
    /**
     * @param string $sameSite
     */
    public function setSameSite(string $sameSite)
    {
        $this->sameSite = $sameSite;
    }
    
    /**
     * Returns the cookie HTTP header string.
     *
     * @return string
     */
    public function getHeaderString(): string
    {
        $parts = [
            $this->name . '=' . rawurlencode($this->value),
            $this->getMaxAgeString(),
            $this->getExpiresString(),
            $this->getDomainString(),
            $this->getPathString(),
            $this->getSecureString(),
            $this->getHttpOnlyString(),
            $this->getDomainString(),
            $this->getSameSiteString()
        ];
    
        // in this case, there is no callback supplied,
        // so all entries of array equal to FALSE will be removed
        $filteredParts = array_filter($parts);
        
        return implode('; ', $filteredParts);
    }
    
    private function getMaxAgeString()
    {
        if ($this->maxAge !== null) {
            return 'Max-Age='. $this->maxAge;
        }
    }
    
    private function getExpiresString()
    {
        if ($this->maxAge !== null) {
            return 'expires=' . gmdate(
                    "D, d-M-Y H:i:s",
                    time() + $this->maxAge
                ) . ' GMT';
        }
    }
    
    private function getDomainString()
    {
        if ($this->domain) {
            return "domain=$this->domain";
        }
    }
    
    private function getPathString()
    {
        if ($this->path) {
            return "path=$this->path";
        }
    }
    
    private function getSecureString()
    {
        if ($this->secure) {
            return 'secure';
        }
    }
    
    private function getHttpOnlyString()
    {
        if ($this->httpOnly) {
            return 'HttpOnly';
        }
    }
    
    private function getSameSiteString()
    {
        if ($this->httpOnly) {
            return 'SameSite='.$this->sameSite;
        }
    }
}
