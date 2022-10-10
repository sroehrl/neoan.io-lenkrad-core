<?php

namespace Neoan\Helper;

use Exception;

class Str
{
    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    public static function toLowerCase(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($string, MB_CASE_LOWER, $encoding);
    }

    public static function toUpperCase(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($string, MB_CASE_UPPER, $encoding);
    }

    public static function toTitleCase(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, $encoding);
    }

    public static function toCamelCase(string $string, string $encoding = 'UTF-8'): string
    {
        $ret = self::caseConverter($string);
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : ucfirst($match);
        }
        return lcfirst(implode('', $ret));
    }

    static function toSnakeCase($string): string
    {
        $ret = self::caseConverter($string);
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    static function toKebabCase($string): string
    {
        $ret = self::caseConverter($string);
        foreach ($ret as &$match) {
            $match = $match == strtolower($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('-', $ret);
    }

    static function toPascalCase($string): string
    {
        $ret = self::caseConverter($string);
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : ucfirst($match);
        }
        return implode('', $ret);
    }

    /**
     * @throws Exception
     */
    static public function randomAlphaNumeric(int $length = 16): string
    {
        return mb_substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * @param $message
     * @param $key
     *
     * @return string
     */
    static function encrypt($message, $key): string
    {
        $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $cipherText = openssl_encrypt(
            $message, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $nonce
        );
        return base64_encode($nonce . $cipherText);

    }

    /**
     * @param $message
     * @param $key
     *
     * @return string
     * @throws Exception
     */
    static function decrypt($message, $key): string
    {
        $message = base64_decode($message, true);
        if ($message === false) {
            throw new Exception('Encryption failure');
        }

        $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $cipherText = mb_substr($message, $nonceSize, null, '8bit');

        return openssl_decrypt(
            $cipherText, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $nonce
        );
    }



    private static function caseConverter($string)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        return $matches[0];
    }
}