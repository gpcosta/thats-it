<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 21/08/2020
 * Time: 22:23
 */

namespace ThatsIt\Security;

use ThatsIt\Exception\PlatformException;

/**
 * Class Cipher
 * @package ThatsIt\Security
 *
 * @note: strongly inspired in https://github.com/ioncube/php-openssl-cryptor/blob/master/src/Cryptor.php
 *
 * @note: generate key with `openssl enc -aes-256-gcm -k secret -P -md sha1`
 */
class Cipher
{
    const FORMAT_RAW = 0;
    const FORMAT_B64 = 1;
    const FORMAT_HEX = 2;
    
    /**
     * @var string
     */
    private $cipherAlgo;
    
    /**
     * @var int
     */
    private $format;
    
    /**
     * @var int
     */
    private $ivNumBytes;
    
    /**
     * Cipher constructor
     * @param string $cipherAlgo
     * @param int $format
     * @throws Exception
     */
    public function __construct(string $cipherAlgo = 'aes-256-ctr', int $format = self::FORMAT_B64)
    {
        if (!in_array($cipherAlgo, openssl_get_cipher_methods(true)))
            throw new ClientException('Cipher algorithm used is unknown cipher algo '.$cipherAlgo);
        
        $this->cipherAlgo = $cipherAlgo;
        $this->format = $format;
        $this->ivNumBytes = openssl_cipher_iv_length($cipherAlgo);
    }
    
    /**
     * Encrypt a string
     * @param  string $content  String to encrypt
     * @param  string $key      Encryption key
     * @return string           The encrypted string
     * @throws PlatformException
     */
    public function encrypt(string $content, string $key)
    {
        // Build an initialisation vector
        $iv = openssl_random_pseudo_bytes($this->ivNumBytes, $isStrongCrypto);
        if (!$isStrongCrypto)
            throw new PlatformException('Cipher didn\'t generate a strong key');
        
        // encrypt content
        $opts = OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($content, $this->cipherAlgo, $key, $opts, $iv);
        
        if ($encrypted === false)
            throw new PlatformException('Cipher encryption has failed: '.openssl_error_string());
        
        // The result comprises the IV and encrypted data
        $res = $iv.$encrypted;
        
        // and format the result if required.
        if ($this->format == self::FORMAT_B64)
            $res = base64_encode($res);
        else if ($this->format == self::FORMAT_HEX)
            $res = unpack('H*', $res)[1];
        
        return $res;
    }
    
    /**
     * Decrypt a string.
     * @param  string $content  String to decrypt
     * @param  string $key      Decryption key
     * @return string           The decrypted string
     * @throws PlatformException
     */
    public function decrypt(string $content, string $key)
    {
        $rawContent = $content;
        
        // Restore the encrypted data if encoded
        if ($this->format == self::FORMAT_B64)
            $rawContent = base64_decode($content);
        else if ($this->format == self::FORMAT_HEX)
            $rawContent = pack('H*', $content);
        
        // and do an integrity check on the size.
        if (strlen($rawContent) < $this->ivNumBytes)
            throw new PlatformException('Cipher error when decrypting - ' .
                'data length ' . strlen($rawContent) . ' is less than iv length '.$this->ivNumBytes);
        
        // Extract the initialisation vector and encrypted data
        $iv = substr($rawContent, 0, $this->ivNumBytes);
        $rawContent = substr($rawContent, $this->ivNumBytes);
    
        // decrypt content
        $opts = OPENSSL_RAW_DATA;
        $res = openssl_decrypt($rawContent, $this->cipherAlgo, $key, $opts, $iv);
    
        if ($res === false)
            throw new PlatformException('Cipher decryption has failed: '.
                openssl_error_string(), 500);
        
        return $res;
    }
}