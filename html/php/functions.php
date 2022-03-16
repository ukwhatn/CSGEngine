<?php

class functions
{
    public static function getSubDomain($url): bool|string
    {
        $domain_array = explode('.', $url);

        if (count($domain_array) > 2) {
            return $domain_array[0];
        } else {
            return "";
        }
    }

    public static function errorLog(Throwable $e)
    {
        error_log($e->getMessage() . " in " . $e->getFile() . "-Line" . $e->getLine());
    }
}