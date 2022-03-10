<?php


namespace console\domain\services\Sitemap;

use api\domain\helpers\localization\LanguageCodeEnum;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\XMLWriter;

/**
 * Class SitemapXmlGenerator
 * @package console\domain\services\Sitemap
 */
class SitemapXmlGenerator
{
    /**
     * @var string
     */
    private string $currentFileName = '';

    /**
     * @var int
     */
    private int $countChildFiles = 0;

    /**
     * @var XMLWriter|null
     */
    private ?XMLWriter $xmlWriter = null;

    /**
     * Method initFile
     * @param string $fileName
     * @return SitemapXmlGenerator
     */
    public function initFile(string $fileName): SitemapXmlGenerator
    {
        $this->xmlWriter = null;
        $this->countChildFiles = 0;
        $this->currentFileName = $this->getFilePath($fileName);
        $this->initSitemapXml();
        return $this;
    }

    /**
     * Method setNextFile
     * @param string $fileName
     * @return SitemapXmlGenerator
     */
    public function setNextFile(string $fileName): SitemapXmlGenerator
    {
        $this->endFile();
        $this->xmlWriter = null;
        $this->countChildFiles++;
        $this->currentFileName = $this->getFilePath($fileName);
        $this->initSitemapXml();
        return $this;
    }

    /**
     * Method getCurrentFileName
     * @return string
     */
    public function getCurrentFileName(): string
    {
        return $this->currentFileName;
    }

    /**
     * Method writeUrlItem
     * @param array $urlItem
     */
    public function writeUrlItem(array $urlItem): void
    {
        $writer = $this->getXmlWriter();
        foreach ($urlItem as $languageCode => $url) {
            $writer->startElement('url');
            $writer->writeElement('loc', $url);
            if (in_array($languageCode, LanguageCodeEnum::getLanguagesList())) {
                $this->writeAlternateItem($languageCode, $urlItem);
            }
            $writer->endElement();
        }
    }

    /**
     * Method writeSitemapItem
     * @param string $fileName
     * @param string $date
     */
    public function writeSitemapItem(string $fileName, string $date): void
    {
        $writer = $this->getXmlWriter();
        $writer->startElement('sitemap');
        $writer->writeElement('loc', $fileName);
        $writer->writeElement('lastmod', $date);
        $writer->endElement();
    }

    /**
     * Method endProcess
     */
    public function endFile(): void
    {
        $writer = $this->getXmlWriter();
        $writer->endDocument();
        $writer->flush();
    }

    /**
     * Method createIndexFile
     * @param string $fileName
     * @param array $childFileNames
     */
    public function createIndexFile(string $fileName, array $childFileNames): void
    {
        $this->currentFileName = $fileName . '.xml';
        $this->initSitemapXml('sitemapindex');
        $dateTime = (new DateTime())->format('Y-m-d\TH:i:s.zP');
        foreach ($childFileNames as $fileName) {
            $this->writeSitemapItem($fileName, $dateTime);
        }
        $this->endFile();
    }

    /**
     * Method initSitemapXml
     * @param string $startElement
     */
    private function initSitemapXml(string $startElement = 'urlset'): void
    {
        $writer = $this->getXmlWriter();
        if (!$writer->openUri($this->getCurrentFileName())) {
            throw new \LogicException('Failed to open tmp file for writing: ' . $this->getCurrentFileName());
        }
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(4);
        $writer->startElement($startElement);
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $writer->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $writer->writeAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd');
    }

    /**
     * Method writeAlternateItem
     * @param string $currentLanguage
     * @param array $urlItem
     */
    private function writeAlternateItem(string $currentLanguage, array $urlItem): void
    {
        if (!isset($urlItem[$currentLanguage])) {
            return;
        }
        unset($urlItem[$currentLanguage]);
        if (empty($urlItem)) {
            return;
        }
        $writer = $this->getXmlWriter();
        $writer->startElement('xhtml:link');
        $writer->writeAttribute('rel', 'alternate');
        $writer->writeAttribute('hreflang', GoogleLanguageCodeEnum::referenceList()[key($urlItem)]);
        $writer->writeAttribute('href', current($urlItem));
        $writer->endElement();
    }

    /**
     * Method getFilePath
     * @param string $fileName
     * @return string
     */
    private function getFilePath(string $fileName): string
    {
        return $fileName . '-' . $this->countChildFiles . '.xml';
    }

    /**
     * Method getXmlWriter
     * @return XMLWriter
     */
    private function getXmlWriter(): XMLWriter
    {
        return $this->xmlWriter ?? $this->xmlWriter = new XMLWriter();
    }
}