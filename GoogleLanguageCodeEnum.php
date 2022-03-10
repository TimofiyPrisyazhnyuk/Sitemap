<?php


namespace console\domain\services\Sitemap;

use api\domain\helpers\localization\LanguageCodeEnum;

/**
 * Class GoogleLanguageCodeEnum
 * @package console\domain\services\Sitemap
 */
class GoogleLanguageCodeEnum
{
    /**
     * Method list
     * @return array
     */
    public static function list(): array
    {
        return [
            static::ua(),
            static::ru()
        ];
    }

    /**
     * Method referenceList
     * @return string[]
     */
    public static function referenceList(): array
    {
        return [
            LanguageCodeEnum::uaISO() => static::ua(),
            LanguageCodeEnum::ruISO() => static::ru(),
        ];
    }

    /**
     * Method ru
     * @return string
     */
    public static function ru(): string
    {
        return 'ru-RU';
    }

    /**
     * Method ua
     * @return string
     */
    public static function ua(): string
    {
        return 'uk-UA';
    }
}