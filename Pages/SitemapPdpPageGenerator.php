<?php


namespace console\domain\services\Sitemap\Pages;

use common\context\application\entity\EntityUrlAlias\EntityUrlAliasInterface;
use common\context\application\entity\Product\ProductInterface;
use common\context\application\repository\Product\ProductRepositoryInterface;
use common\context\Url\FrontendUrlManagerInterface;
use Generator;

/**
 * Class SitemapPdpPageGenerator
 * @package console\domain\services\Sitemap\Pages
 */
class SitemapPdpPageGenerator implements SitemapPageGeneratorInterface
{
    /**
     * PdpPage constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param FrontendUrlManagerInterface $frontendUrlManager
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FrontendUrlManagerInterface $frontendUrlManager
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getItem(array $options = []): Generator
    {
        $productPages = $this->productRepository->getAvailableProductsQuery();
        /** @var ProductInterface $productPage */
        foreach ($productPages->each() as $productPage) {
            $productUrlAliases = $productPage->getAliasRelationIndexedByLanguageId()->all();
            yield $this->prepareProductUrlItem($productUrlAliases);
        }
    }

    /**
     * Method prepareProductUrlItem
     * @param array $productUrlAliases
     * @return array
     */
    private function prepareProductUrlItem(array $productUrlAliases): array
    {
        $productUrlItem = [];
        /** @var EntityUrlAliasInterface $productUrlAlias */
        foreach ($productUrlAliases as $languageCode => $productUrlAlias) {
            $productUrlItem[$languageCode] = $this->frontendUrlManager->createFrontendAbsoluteUrl(DIRECTORY_SEPARATOR . $productUrlAlias->getAlias());
        }
        return $productUrlItem;
    }
}