<?php


namespace console\domain\services\Sitemap\Pages;

use api\domain\helpers\localization\LanguageCodeEnum;
use common\context\application\entity\Attributes\AttributeValueStatusEnum;
use common\context\application\repository\ProductAttribute\ProductAttributeValueRepositoryInterface;
use common\context\Catalog\service\Url\CatalogUrlGeneratorInterface;
use common\context\Catalog\storage\CatalogProductStorageInterface;
use common\context\L10n\LanguageStorageInterface;
use common\context\PageMetadata\repository\metadata\MetadataRepositoryInterface;
use common\context\Url\FrontendUrlManagerInterface;
use common\infrastructure\entity\application\ProductAttribute\ProductAttributeValueXref;
use common\persistence\Gateway\ActiveRecord\GatewayNameEnum;
use Generator;

/**
 * Class SitemapCatalogPageGenerator
 * @package console\domain\services\Sitemap\Pages
 */
class SitemapCatalogPageGenerator implements SitemapPageGeneratorInterface
{
    /**
     * @var array
     */
    private array $availableAttributeValueIds = [];

    /**
     * CatalogPage constructor.
     * @param ProductAttributeValueRepositoryInterface $attributeValueRepository
     * @param CatalogUrlGeneratorInterface $catalogUrlGenerator
     * @param FrontendUrlManagerInterface $frontendUrlManager
     * @param MetadataRepositoryInterface $metadataRepository
     * @param LanguageStorageInterface $languageStorage
     * @param CatalogProductStorageInterface $catalogProductStorage
     */
    public function __construct(
        private ProductAttributeValueRepositoryInterface $attributeValueRepository,
        private CatalogUrlGeneratorInterface $catalogUrlGenerator,
        private FrontendUrlManagerInterface $frontendUrlManager,
        private MetadataRepositoryInterface $metadataRepository,
        private LanguageStorageInterface $languageStorage,
        private CatalogProductStorageInterface $catalogProductStorage,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getItem(array $options = []): Generator
    {
        foreach ($options['attributeExternalIds'] as $externalIds) {
            foreach ($this->getAttributesCombinations($externalIds) as $combinationItem) {
                if (!is_array($combinationItem)) {
                    $combinationItem = [$combinationItem];
                }
                if (!$this->isExistProductByCombination($combinationItem)) {
                    continue;
                }
                $catalogItem = $this->generateCatalogUrl($combinationItem);
                if ([] === $catalogItem) {
                    continue;
                }
                yield $catalogItem;
            }
        }
    }

    /**
     * Method generateCatalogUrl
     * @param array $attributeValueIds
     * @return array
     */
    private function generateCatalogUrl(array $attributeValueIds): array
    {
        $attributeValues = $this->attributeValueRepository->getListByIds($attributeValueIds);
        $catalogUrlItem = [];
        foreach (LanguageCodeEnum::getLanguagesList() as $language) {
            $this->languageStorage->switchToLanguage($language);
            $catalogUrlPath = $this->catalogUrlGenerator->generateForAttributeValues(...$attributeValues);
            $metaData = $this->metadataRepository->getForUrl($catalogUrlPath, $language);
            if (null !== $metaData && ($metaData->isIndex() === false || $metaData->isFollow() === false)) {
                continue;
            }
            $catalogUrlItem[$language] = $this->frontendUrlManager->createFrontendAbsoluteUrl($catalogUrlPath);
        }
        return $catalogUrlItem;
    }

    /**
     * Method getAttributesCombinations
     * @param array $attributeExternalIds
     * @return array
     */
    private function getAttributesCombinations(array $attributeExternalIds): array
    {
        $attributeValueCombinations = [];
        foreach ($attributeExternalIds as $serviceName => $externalId) {
            if (is_string($serviceName) && !empty($serviceName)) {
                $attributeValue = $this->attributeValueRepository->getByServiceName($serviceName);
                if (null !== $attributeValue) {
                    $attributeValueCombinations[$externalId] = [$attributeValue->getId()];
                    continue;
                }
            }
            $attributeValuesIds = array_column($this->attributeValueRepository->getListByAttributeExternalId($externalId), 'id');
            $attributeValuesIds = $this->filterAttributeValueIds($attributeValuesIds);
            $attributeValueCombinations[$externalId] = $attributeValuesIds;
        }
        return $this->getCombinations(array_values($attributeValueCombinations));
    }

    /**
     * Method filterAttributeValueIds
     * @param array $attributeValueIds
     * @return array
     */
    private function filterAttributeValueIds(array $attributeValueIds): array
    {
        $filteredAttributeValueIds = [];
        foreach ($attributeValueIds as $attributeValueId) {
            if (in_array($attributeValueId, $this->availableAttributeValueIds)) {
                $filteredAttributeValueIds[] = $attributeValueId;
            }
        }
        return $filteredAttributeValueIds;
    }

    /**
     * Method isExistProductByCombination
     * @param array $combinationItem
     * @return bool
     */
    private function isExistProductByCombination(array $combinationItem): bool
    {
        return $this->catalogProductStorage->isExistProductByAttributeValueIds($combinationItem);
    }

    /**
     * Method getCombinations
     * @param array $attributeValueIds
     * @param int $i
     * @return array
     */
    private function getCombinations(array $attributeValueIds, $i = 0): array
    {
        if (!isset($attributeValueIds[$i])) {
            return [];
        }
        if ($i == count($attributeValueIds) - 1) {
            return $attributeValueIds[$i];
        }
        $subsequentCombinations = $this->getCombinations($attributeValueIds, $i + 1);
        $allCombinations = [];
        foreach ($attributeValueIds[$i] as $combination) {
            foreach ($subsequentCombinations as $subsequentCombination) {
                $allCombinations[] = is_array($subsequentCombination)
                    ? array_merge([$combination], $subsequentCombination)
                    : [$combination, $subsequentCombination];
            }
        }
        return $allCombinations;
    }
}