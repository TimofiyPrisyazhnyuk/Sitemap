<?php


namespace console\domain\services\Sitemap;

use common\context\Url\FrontendUrlManagerInterface;
use console\domain\services\Sitemap\Pages\SitemapPageGeneratorInterface;
use RuntimeException;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * Class SitemapGenerator
 * @package console\domain\services\Sitemap
 */
class SitemapGenerator implements SitemapGeneratorInterface
{
    /**
     * @var array
     */
    private array $childFileNames = [];

    /**
     * @var string|null
     */
    private ?string $currentFileName = null;

    /**
     * @var int
     */
    private int $countItems = 0;

    /**
     * SitemapGenerator constructor.
     * @param SitemapConfig $sitemapConfig
     * @param SitemapXmlGenerator $sitemapXmlGenerator
     * @param FrontendUrlManagerInterface $frontendUrlManager
     */
    public function __construct(
        private SitemapConfig $sitemapConfig,
        private SitemapXmlGenerator $sitemapXmlGenerator,
        private FrontendUrlManagerInterface $frontendUrlManager,

    )
    {
    }

    /**
     * SitemapGenerator destructor
     */
    public function __destruct()
    {
        $this->clearTmpDir();
    }

    /**
     * @inheritDoc
     */
    public function generateXml(): void
    {
        $this->initSitemapTmpDir();
        $sitemapContentConfig = $this->sitemapConfig->getContentConfig();
        foreach ($sitemapContentConfig as $childFileName => $pageContentConfig) {
            $this->sitemapXmlGenerator->initFile($childFileName);
            $this->initCurrentFile();
            /** @var SitemapPageGeneratorInterface $pageGenerator */
            foreach ($pageContentConfig as $pageConfig) {
                list($pageGenerator, $itemOptions) = $this->getPageItemFromConfig($pageConfig);
                foreach ($pageGenerator->getItem($itemOptions) as $urlItem) {
                    if ($this->countItems >= $this->sitemapConfig->getMaxCountItems() || $this->isFileSizeExceeded()) {
                        $this->sitemapXmlGenerator->setNextFile($childFileName);
                        $this->initCurrentFile();
                    }
                    $this->sitemapXmlGenerator->writeUrlItem($urlItem);
                    $this->countItems += count($urlItem);
                    $this->logProgress();
                }
            }
            $this->sitemapXmlGenerator->endFile();
        }
        $this->sitemapXmlGenerator->createIndexFile($this->sitemapConfig->getIndexSitemapFileName(), $this->getChildFileUrls());
        $this->copyTmpSitemapFilesToStorage();
    }

    /**
     * Method copyTmpSitemapFilesToStorage
     */
    private function copyTmpSitemapFilesToStorage(): void
    {
        FileHelper::removeDirectory($this->sitemapConfig->getSitemapStoragePath());
        FileHelper::copyDirectory($this->sitemapConfig->getCurrentTmpSitemapDir(), $this->sitemapConfig->getSitemapStoragePath());
        if (!is_dir($this->sitemapConfig->getSitemapStoragePath())) {
            throw new RuntimeException('Failed to copy generated sitemap files to storage, tmpDir: '
                . $this->sitemapConfig->getCurrentTmpSitemapDir(), ' destination path: ' . $this->sitemapConfig->getSitemapStoragePath());
        }
    }

    /**
     * Method setChildProcess
     */
    private function initCurrentFile(): void
    {
        $this->countItems = 0;
        $this->currentFileName = $this->sitemapXmlGenerator->getCurrentFileName();
        $this->childFileNames[] = $this->currentFileName;
    }

    /**
     * Method isFileSizeExceeded
     * @return bool
     */
    private function isFileSizeExceeded(): bool
    {
        $fileSize = @filesize($this->currentFileName);
        return $fileSize > $this->sitemapConfig->getMaxFileSize();
    }

    /**
     * Method initSitemapTmpDir
     * @throws ErrorException
     * @throws Exception
     */
    private function initSitemapTmpDir(): void
    {
        $this->clearTmpDir();
        FileHelper::createDirectory($this->sitemapConfig->getCurrentTmpSitemapDir());
    }

    /**
     * Method clearTmpDir
     * @throws ErrorException
     */
    private function clearTmpDir(): void
    {
        if (is_dir($this->sitemapConfig->getCurrentTmpSitemapDir())) {
            FileHelper::removeDirectory($this->sitemapConfig->getCurrentTmpSitemapDir());
        }
    }

    /**
     * Method getChildFileUrls
     * @return array
     */
    private function getChildFileUrls(): array
    {
        $childFileUrls = [];
        foreach ($this->childFileNames as $fileName) {
            $childFileUrls[] = $this->frontendUrlManager->createFrontendAbsoluteUrl(basename($fileName));
        }
        return $childFileUrls;
    }

    /**
     * Method getPageItemFromConfig
     * @param array $pageConfig
     * @return array
     * @throws InvalidConfigException
     */
    private function getPageItemFromConfig(array $pageConfig): array
    {
        if (!isset($pageConfig['generator'])) {
            throw new InvalidConfigException('Sitemap config is invalid, generator item is not set');
        }
        $itemOptions = $pageConfig['options'] ?? [];
        return [$pageConfig['generator'], $itemOptions];
    }

    /**
     * Method logProgress
     */
    private function logProgress(): void
    {
        echo '.';
    }
}