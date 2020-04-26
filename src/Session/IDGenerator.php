<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/04/2020
 * Time: 09:28
 */

namespace ThatsIt\Session;

/**
 * Class IDGenerator
 * @package ThatsIt\Session
 */
class IDGenerator
{
    /**
     * Generates a random Base 64 ID
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function generateBase64ID(int $length)
    {
        $token = "";
        // remove vowels to avoid words like "penis" on others
        //$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet = "BCDFGHJKLMNPQRSTVWXYZ";
        //$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "bcdfghjklmnpqrstvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);
    
        for ($i = 0; $i < $length; $i++)
            $token .= $codeAlphabet[random_int(0, $max-1)];
    
        return $token;
    }
}