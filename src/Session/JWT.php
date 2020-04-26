<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/04/2020
 * Time: 15:12
 */

namespace ThatsIt\Session;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

/**
 * Class JWT
 * @package ThatsIt\Session
 *
 * Simple class to take care of JWT tokens.
 * If more info about methods is needed see \Firebase\JWT\JWT
 *
 * \Firebase\JWT\JWT also offers more methods so if you need
 * more control is needed over the processes related with JWT tokens
 * please use Firebase library instead of this class
 */
class JWT
{
    /**
     * Replica from $leeway variable from \Firebase\JWT\JWT
     *
     * When checking nbf, iat or expiration times,
     * we want to provide some extra leeway time to
     * account for clock skew.
     */
    public static $leeway = 0;
    
    /**
     * Replica from $timestamp variable from \Firebase\JWT\JWT
     *
     * It is used in decode method from \Firebase\JWT\JWT to
     * verify all times (nbf, iat or expiration time)
     *
     * Allow the current timestamp to be specified.
     * Useful for fixing a value within unit testing.
     *
     * Will default to PHP time() value if null.
     */
    public static $timestamp = null;
    
    /**
     * @var array
     */
    private $payload;
    
    /**
     * @var string
     */
    private $issuer;
    
    /**
     * @var string
     */
    private $subject;
    
    /**
     * @var string
     */
    private $audience;
    
    /**
     * @var int
     */
    private $expirationTime;
    
    /**
     * @var int
     */
    private $notBefore;
    
    /**
     * @var int
     */
    private $issuedAt;
    
    /**
     * @var string
     */
    private $jwtId;
    
    /**
     * JWTToken constructor.
     * @param array $payload
     * @param string|null $issuer
     * @param string|null $subject
     * @param string|null $audience
     * @param int|null $expirationTime
     * @param int|null $notBefore
     * @param string|null $issuedAt
     * @param string|null $jwtId
     */
    public function __construct(array $payload, string $issuer = null, string $subject = null,
                                string $audience = null, int $expirationTime = null, int $notBefore = null,
                                string $issuedAt = null, string $jwtId = null)
    {
        $this->payload = $payload;
        $this->issuer = $issuer;
        $this->subject = $subject;
        $this->audience = $audience;
        $this->expirationTime = $expirationTime;
        $this->notBefore = $notBefore;
        $this->issuedAt = $issuedAt;
        $this->jwtId = $jwtId;
    }
    
    /**
     * @param string                    $jwt            The JWT
     * @param string|array|resource     $key            The key, or map of keys.
     *                                                  If the algorithm used is asymmetric, this is the public key
     * @param array                     $allowed_algs   List of supported verification algorithms
     *                                                  Supported algorithms are 'ES256', 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', and 'RS512'
     * @return JWT
     *
     * @throws UnexpectedValueException    Provided JWT was invalid
     * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
     * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
     * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
     * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
     */
    public static function decode($jwt, $key, array $allowed_algs = array()): self
    {
        \Firebase\JWT\JWT::$leeway = self::$leeway;
        \Firebase\JWT\JWT::$timestamp = self::$timestamp;
        $allPayload = \Firebase\JWT\JWT::decode($jwt, $key, $allowed_algs);
        
        $issuer = null;
        if (isset($allPayload->iss)) {
            $issuer = $allPayload->iss;
            unset($allPayload->iss);
        }
        $subject = null;
        if (isset($allPayload->sub)) {
            $subject = $allPayload->sub;
            unset($allPayload->sub);
        }
        $audience = null;
        if (isset($allPayload->aud)) {
            $audience = $allPayload->aud;
            unset($allPayload->aud);
        }
        $expirationTime = null;
        if (isset($allPayload->exp)) {
            $expirationTime = $allPayload->exp;
            unset($allPayload->exp);
        }
        $notBefore = null;
        if (isset($allPayload->nbf)) {
            $notBefore = $allPayload->nbf;
            unset($allPayload->nbf);
        }
        $issuedAt = null;
        if (isset($allPayload->iat)) {
            $issuedAt = $allPayload->iat;
            unset($allPayload->iat);
        }
        $jwtId = null;
        if (isset($allPayload->jti)) {
            $jwtId = $allPayload->jti;
            unset($allPayload->jti);
        }
        
        return new JWT((array) $allPayload, $issuer, $subject, $audience,
            $expirationTime, $notBefore, $issuedAt, $jwtId);
    }
    
    /**
     * @param string        $key        The secret key.
     *                                  If the algorithm used is asymmetric, this is the private key
     * @param string        $alg        The signing algorithm.
     *                                  Supported algorithms are 'ES256', 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', and 'RS512'
     * @param mixed         $keyId
     * @param array         $head       An array with header elements to attach
     *
     * @return string A signed JWT
     */
    public function encode($key, $alg = 'HS256', $keyId = null, $head = null): string
    {
        $payload = $this->payload;
        if (isset($this->issuer)) $payload['iss'] = $this->issuer;
        if (isset($this->subject)) $payload['sub'] = $this->subject;
        if (isset($this->audience)) $payload['aud'] = $this->audience;
        if (isset($this->expirationTime)) $payload['exp'] = $this->expirationTime;
        if (isset($this->notBefore)) $payload['nbf'] = $this->notBefore;
        if (isset($this->issuedAt)) $payload['iat'] = $this->issuedAt;
        if (isset($this->jwtId)) $payload['jti'] = $this->jwtId;
    
        \Firebase\JWT\JWT::$leeway = self::$leeway;
        \Firebase\JWT\JWT::$timestamp = self::$timestamp;
        return \Firebase\JWT\JWT::encode($payload, $key, $alg, $keyId, $head);
    }
    
    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
    
    /**
     * @return string
     */
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }
    
    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }
    
    /**
     * @return string
     */
    public function getAudience(): ?string
    {
        return $this->audience;
    }
    
    /**
     * @return int
     */
    public function getExpirationTime(): ?int
    {
        return $this->expirationTime;
    }
    
    /**
     * @return int
     */
    public function getNotBefore(): ?int
    {
        return $this->notBefore;
    }
    
    /**
     * @return int
     */
    public function getIssuedAt(): ?int
    {
        return $this->issuedAt;
    }
    
    /**
     * @return string
     */
    public function getJwtId(): ?string
    {
        return $this->jwtId;
    }
    
    /**
     * @param array $payload
     * @return JWT
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }
    
    /**
     * @param string $issuer
     * @return JWT
     */
    public function setIssuer(string $issuer): self
    {
        $this->issuer = $issuer;
        return $this;
    }
    
    /**
     * @param string $subject
     * @return JWT
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * @param string $audience
     * @return JWT
     */
    public function setAudience(string $audience): self
    {
        $this->audience = $audience;
        return $this;
    }
    
    /**
     * @param int $expirationTime
     * @return JWT
     */
    public function setExpirationTime(int $expirationTime): self
    {
        $this->expirationTime = $expirationTime;
        return $this;
    }
    
    /**
     * @param int $notBefore
     * @return JWT
     */
    public function setNotBefore(int $notBefore): self
    {
        $this->notBefore = $notBefore;
        return $this;
    }
    
    /**
     * @param int $issuedAt
     * @return JWT
     */
    public function setIssuedAt(int $issuedAt): self
    {
        $this->issuedAt = $issuedAt;
        return $this;
    }
    
    /**
     * @param string $jwtId
     * @return JWT
     */
    public function setJwtId(string $jwtId): self
    {
        $this->jwtId = $jwtId;
        return $this;
    }
}