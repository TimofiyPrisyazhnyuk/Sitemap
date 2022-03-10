<?php


namespace console\domain\services\Sitemap\Pages;

use common\context\application\entity\PageStructureRule\PageStructureRuleInterface;
use common\context\application\repository\PageStructureRule\PageStructureRuleRepositoryInterface;
use common\context\Url\FrontendUrlManagerInterface;
use common\infrastructure\routing\type\pageTypes\PageTypeEnum;
use Generator;

/**
 * Class SitemapLandingPageGenerator
 * @package console\domain\services\Sitemap\Pages
 */
class SitemapLandingPageGenerator implements SitemapPageGeneratorInterface
{
    /**
     * LandingPage constructor.
     * @param PageStructureRuleRepositoryInterface $pageStructureRuleRepository
     * @param FrontendUrlManagerInterface $frontendUrlManager
     */
    public function __construct(
        private PageStructureRuleRepositoryInterface $pageStructureRuleRepository,
        private FrontendUrlManagerInterface $frontendUrlManager
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getItem(array $options = []): Generator
    {
        $landingPages = $this->pageStructureRuleRepository->getQueryByPageType(PageTypeEnum::landing());
        /** @var PageStructureRuleInterface $landingPage */
        foreach ($landingPages->each() as $landingPage) {
            yield $this->convertToAbsoluteUrl($landingPage->getUrlList());
        }
    }

    /**
     * Method convertToAbsoluteUrl
     * @param array $urlList
     * @return array
     */
    private function convertToAbsoluteUrl(array $urlList): array
    {
        foreach ($urlList as $languageCode => $url) {
            $urlList[$languageCode] = $this->frontendUrlManager->createFrontendAbsoluteUrl($url);
        }
        return $urlList;
    }
}