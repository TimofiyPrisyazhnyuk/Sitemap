<?php


namespace console\domain\services\Sitemap;

/**
 * Interface SitemapGeneratorInterface
 * @package console\domain\services\Sitemap
 */
interface SitemapGeneratorInterface
{
    /**
     * Method generateXml
     */
    public function generateXml(): void;

}