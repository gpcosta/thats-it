<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/04/2020
 * Time: 15:54
 */

namespace ThatsIt\Session;

use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\Cookie;
use ThatsIt\Response\HttpResponse;

/**
 * Class CSRFToken
 * @package ThatsIt\Session
 *
 * CSRF stands for 'Cross-Site Request Forgery'
 * and this class pretends give protection to this type of attacks
 */
class CSRFToken
{
    /**
     * Singleton
     *
     * @var null|CSRFToken
     */
    private static $currentCSRFToken = null;
    
    /**
     * This const held the name of the Cookie that
     * will be have the CSRFToken
     *
     * This name was chosen because there is frameworks that uses this name
     * ex: Laravel, Angular, ...
     */
    private const COOKIE_CSRF = 'X-XSRF-TOKEN';
    
    /**
     * @var string
     */
    private $token;
    
    /**
     * CSRFToken constructor.
     * @param string $token
     */
    private function __construct(string $token)
    {
        $this->token = $token;
    }
    
    /**
     * Get current CSRF Token from Cookies of the provided Http Request
     * If none CSRF Token exists in Http Request, returns null
     *
     * @param HttpRequest $request
     * @return null|CSRFToken
     */
    public static function getCSRFTokenFromCookies(HttpRequest $request): ?self
    {
        $csrfToken = $request->getCookie(self::COOKIE_CSRF);
        return $csrfToken ? new CSRFToken($csrfToken) : null;
    }
    
    /**
     * Sets a Cookie by the name of self::COOKIE_CSRF with a new CSRFToken
     * in the response that should be sent to the client
     *
     * @param HttpResponse $response
     * @return HttpResponse
     * @throws \Exception
     */
    public static function createNewCSRFToken(HttpResponse $response): HttpResponse
    {
        self::$currentCSRFToken = new CSRFToken(IDGenerator::generateBase64ID(32));
        $cookie = new Cookie(self::COOKIE_CSRF, self::$currentCSRFToken->token);
        $response->addCookie($cookie);
        return $response;
    }
    
    /**
     * Validates if there was CSRF
     *
     * @param string $csrfToken - should be the token that came from a parameter
     *                            and not the one that came from cookies
     * @return bool
     */
    public static function validate(string $csrfToken): bool
    {
        return self::$currentCSRFToken->token === $csrfToken;
    }
}