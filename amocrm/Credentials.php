<?php
/**
 * Created by PhpStorm.
 * User: mrcake
 * Date: 12/3/17
 * Time: 11:58 PM
 */

namespace amocrm;


class Credentials
{
    const USERNAME_KEY = 'USER_LOGIN';
    const USERHASH_KEY = 'USER_HASH';

    private static $credentials;

    public static function getCredentials()
    {
        return self::$credentials;
    }

    public static function getUserData()
    {
        return [
            self::USERNAME_KEY => self::$credentials['login'],
            self::USERHASH_KEY => self::$credentials['key'],
        ];
    }

    public static function getSubdomain()
    {
        return self::$credentials['subdomain'];
    }

    public static function getCookieFile()
    {
        return self::$credentials['cookie_file'];
    }

    public static function setCredentials(array $credentials)
    {
        self::$credentials = $credentials;
    }

    public static function getTaskElementTypes()
    {
        return [
            'task_element_type_contact' => self::$credentials['task_element_type_contact'],
            'task_element_type_deal' => self::$credentials['task_element_type_deal'],
            'task_element_type_company' => self::$credentials['task_element_type_company'],
        ];
    }
}
