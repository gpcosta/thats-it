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
     * @param bool $removeVowels - remove vowels to avoid words like "penis" on others
     * @return string
     * @throws \Exception
     */
    public static function generateBase64ID(int $length, bool $removeVowels = false)
    {
        $token = "";
        // remove vowels to avoid words like "penis" on others
        if ($removeVowels) {
            $codeAlphabet = "BCDFGHJKLMNPQRSTVWXYZ";
            $codeAlphabet .= "bcdfghjklmnpqrstvwxyz";
        } else {
            $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        }
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);
        
        for ($i = 0; $i < $length; $i++)
            $token .= $codeAlphabet[random_int(0, $max-1)];
        
        return $token;
    }
}