<?php


namespace console\domain\services\Sitemap;

use common\context\application\entity\ProductAttributes\ProductAttributesExternalIdEnum;
use common\context\Catalog\entity\Product\ProductCategoryCodeEnum;
use console\domain\services\Sitemap\Pages\SitemapLandingPageGenerator;
use console\domain\services\Sitemap\Pages\SitemapPdpPageGenerator;
use console\domain\services\Sitemap\Pages\SitemapCatalogPageGenerator;
use console\domain\services\Sitemap\Pages\SitemapStaticPageGenerator;
use DateTime;
use Yii;

/**
 * Class SitemapConfig
 * @package console\domain\services\Sitemap
 */
class SitemapConfig
{
    /**
     * @var string
     */
    public string $indexSitemapFileName = 'sitemap';

    /**
     * @var string
     */
    public string $sitemapDirName = 'sitemap';

    /**
     * @var string|null
     */
    private ?string $currentTmpSitemapDir = null;

    /**
     * @const int
     */
    private const MAX_COUNT_ITEMS = 49998;

    /**
     * @const int
     */
    private const MAX_FILE_SIZE = 49;//mb

    /**
     * SitemapConfig constructor.
     * @param SitemapLandingPageGenerator $sitemapLandingPageGenerator
     * @param SitemapStaticPageGenerator $sitemapStaticPageGenerator
     * @param SitemapPdpPageGenerator $sitemapPdpPageGenerator
     * @param SitemapCatalogPageGenerator $sitemapCatalogPageGenerator
     */
    public function __construct(
        private SitemapLandingPageGenerator $sitemapLandingPageGenerator,
        private SitemapStaticPageGenerator $sitemapStaticPageGenerator,
        private SitemapPdpPageGenerator $sitemapPdpPageGenerator,
        private SitemapCatalogPageGenerator $sitemapCatalogPageGenerator,
    )
    {
    }

    /**
     * Method getContentConfig
     * @return array
     */
    public function getContentConfig(): array
    {
        return [
            $this->getFileNameName('sitemap-osnovnye') => [
                ['generator' => $this->sitemapLandingPageGenerator],
                ['generator' => $this->sitemapStaticPageGenerator],
                [
                    'generator' => $this->sitemapCatalogPageGenerator,
                    'options' => [
                        'attributeExternalIds' => [
                            // list of categories for URL builder
                        ]
                    ]
                ]
            ],
            $this->getFileNameName('sitemap-odezhda') => [
                [
                    'generator' => $this->sitemapCatalogPageGenerator,
                    'options' => [
                        'attributeExternalIds' => [
                          // list of categories for URL builder
                        ]
                    ]
                ],
            ],
            $this->getFileNameName('sitemap-obuv') => [
                [
                    'generator' => $this->sitemapCatalogPageGenerator,
                    'options' => [
                        'attributeExternalIds' => [
                            // list of categories for URL builder
                        ]
                    ]
                ],
            ],
            $this->getFileNameName('sitemap-aksessuary') => [
                [
                    'generator' => $this->sitemapCatalogPageGenerator,
                    'options' => [
                        'attributeExternalIds' => [
                            // list of categories for URL builder
                        ]
                    ]
                ],
            ],
            $this->getFileNameName('sitemap-vidy-sporta') => [
                [
                    'generator' => $this->sitemapCatalogPageGenerator,
                    'options' => [
                        'attributeExternalIds' => [
                            // list of categories for URL builder
                        ]
                    ]
                ],
            ],
            $this->getFileNameName('sitemap-kartochki-tovarov') => [
                ['generator' => $this->sitemapPdpPageGenerator],
            ],
        ];
    }

    /**
     * Method getMaxCountItems
     * @return int
     */
    public function getMaxCountItems(): int
    {
        return static::MAX_COUNT_ITEMS;
    }

    /**
     * Method getCurrentTmpSitemapDir
     * @return string
     */
    public function getCurrentTmpSitemapDir(): string
    {
        if ($this->currentTmpSitemapDir === null) {
            $dateTime = new DateTime();
            $this->currentTmpSitemapDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $dateTime->format('Y-m-d') . DIRECTORY_SEPARATOR;
        }
        return $this->currentTmpSitemapDir;
    }

    /**
     * Method getMaxFileSize
     * @return int
     */
    public function getMaxFileSize(): int
    {
        return static::MAX_FILE_SIZE * 1024 * 1024;
    }

    /**
     * Method getSitemapStoragePath
     * @return string
     */
    public function getSitemapStoragePath(): string
    {
        return Yii::getAlias('@console/runtime/' . $this->sitemapDirName);
    }

    /**
     * Method getIndexSitemapFileName
     * @return string
     */
    public function getIndexSitemapFileName(): string
    {
        return $this->getFileNameName($this->indexSitemapFileName);
    }

    /**
     * Method getChildItemName
     * @param string $processName
     * @return string
     */
    private function getFileNameName(string $processName): string
    {
        return $this->getCurrentTmpSitemapDir() . $processName;
    }
}