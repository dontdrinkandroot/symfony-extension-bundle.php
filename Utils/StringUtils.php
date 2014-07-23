<?php


namespace Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils;


class StringUtils
{

    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    public static function getFirstChar($str)
    {
        $length = strlen($str);
        if ($length === 0) {
            return '';
        }

        return substr($str, 0, 1);
    }

    public static function getLastChar($str)
    {
        $length = strlen($str);
        if ($length === 0) {
            return '';
        }

        return substr($str, $length - 1, 1);
    }

}