<?php


namespace console\domain\services\Sitemap\Pages;

use common\context\Url\FrontendUrlManagerInterface;
use Generator;

/**
 * Class SitemapStaticPageGenerator
 * @package console\domain\services\Sitemap\Pages
 */
class SitemapStaticPageGenerator implements SitemapPageGeneratorInterface
{
    /**
     * StaticPage constructor.
     * @param FrontendUrlManagerInterface $frontendUrlManager
     */
    public function __construct(
        private FrontendUrlManagerInterface $frontendUrlManager
    )
    {
    }

    /**
     * Method pagesList
     * @return array[]
     */
    private function pagesList(): array
    {
        return [
            // Example create absolute URL for static pages.
            // $this->frontendUrlManager->createFrontendAbsoluteUrl('/about-us'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getItem(array $options = []): Generator
    {
        foreach ($this->pagesList() as $url) {
            yield [$url];
        }
    }
}