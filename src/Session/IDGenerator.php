<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/04/2020
 * Time: 09:28
 */

namespace ThatsIt\Session;

use Godruoyi\Snowflake\Snowflake;
use ThatsIt\Configurations\Configurations;

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
     * @param bool $removeHyphensAndUnderscores - remove hyphens and underscores
     * @return string
     * @throws \Exception
     */
    public static function generateBase64ID(int $length, bool $removeVowels = false,
                                            bool $removeHyphensAndUnderscores = false)
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
        
        if (!$removeHyphensAndUnderscores)
            $codeAlphabet .= "-_";
        
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);
        
        for ($i = 0; $i < $length; $i++)
            $token .= $codeAlphabet[random_int(0, $max-1)];
        
        return $token;
    }
    
    /**
     * Generate a Twitter Snowflake ID
     *
     * It uses the following library:
     *      - https://github.com/godruoyi/php-snowflake
     *
     * To understand why this type of ID is important, please see the following article:
     *      - https://www.callicoder.com/distributed-unique-id-sequence-number-generator/
     *
     * @return int
     * @throws \ThatsIt\Exception\PlatformException
     */
    public static function generateSnowflakeID(): int
    {
        $snowflakeConfig = Configurations::getSnowflakeConfig();
        $snowflake = new Snowflake($snowflakeConfig['datacenterId'], $snowflakeConfig['workerId']);
        $snowflake->setStartTimeStamp(strtotime('2020-06-01')*1000);
        return $snowflake->id();
    }
}