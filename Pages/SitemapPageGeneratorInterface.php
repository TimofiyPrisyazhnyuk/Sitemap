<?php


namespace console\domain\services\Sitemap\Pages;

use Generator;

/**
 * Class SitemapPageGeneratorInterface
 * @package console\domain\services\Sitemap\Pages
 */
interface SitemapPageGeneratorInterface
{
    /**
     * Method getItem
     * @param array $options
     * @return Generator
     */
    public function getItem(array $options = []): Generator;
}