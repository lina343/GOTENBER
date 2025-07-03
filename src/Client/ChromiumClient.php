<?php

declare(strict_types=1);

namespace GotenbergPHP\Client;

use GotenbergPHP\Exception\ConversionException;

/**
 * Client for Chromium-based PDF conversions
 */
class ChromiumClient
{
    private GotenbergClient $client;

    public function __construct(GotenbergClient $client)
    {
        $this->client = $client;
    }

    /**
     * Convert a URL to PDF
     */
    public function convertUrlToPdf(string $url, array $options = []): string
    {
        $formData = array_merge(['url' => $url], $this->buildPageOptions($options));
        
        $response = $this->client->sendMultipartRequest('/forms/chromium/convert/url', $formData);
        
        return $response->getBody()->getContents();
    }

    /**
     * Convert HTML content to PDF
     */
    public function convertHtmlToPdf(string $htmlContent, array $additionalFiles = [], array $options = []): string
    {
        $files = ['files' => ['content' => $htmlContent, 'filename' => 'index.html']];
        
        // Add additional files (CSS, images, etc.)
        foreach ($additionalFiles as $filename => $content) {
            $files['files'][] = ['content' => $content, 'filename' => $filename];
        }

        $formData = $this->buildPageOptions($options);
        
        $response = $this->client->sendMultipartRequest('/forms/chromium/convert/html', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Convert HTML file to PDF
     */
    public function convertHtmlFileToPdf(string $htmlFilePath, array $additionalFilePaths = [], array $options = []): string
    {
        if (!file_exists($htmlFilePath)) {
            throw new ConversionException("HTML file not found: {$htmlFilePath}");
        }

        $files = ['files' => $htmlFilePath];
        
        // Add additional files
        foreach ($additionalFilePaths as $filePath) {
            if (!file_exists($filePath)) {
                throw new ConversionException("Additional file not found: {$filePath}");
            }
            $files['files'][] = $filePath;
        }

        $formData = $this->buildPageOptions($options);
        
        $response = $this->client->sendMultipartRequest('/forms/chromium/convert/html', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Convert Markdown to PDF
     */
    public function convertMarkdownToPdf(string $markdownContent, string $htmlTemplate, array $options = []): string
    {
        $files = [
            'files' => [
                ['content' => $htmlTemplate, 'filename' => 'index.html'],
                ['content' => $markdownContent, 'filename' => 'content.md']
            ]
        ];

        $formData = $this->buildPageOptions($options);
        
        $response = $this->client->sendMultipartRequest('/forms/chromium/convert/markdown', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Take a screenshot of a URL
     */
    public function screenshotUrl(string $url, array $options = []): string
    {
        $formData = array_merge(['url' => $url], $this->buildScreenshotOptions($options));
        
        $response = $this->client->sendMultipartRequest('/forms/chromium/screenshot/url', $formData);
        
        return $response->getBody()->getContents();
    }

    /**
     * Build page options for PDF conversion
     */
    private function buildPageOptions(array $options): array
    {
        $defaults = [
            'paperWidth' => 8.5,
            'paperHeight' => 11,
            'marginTop' => 0.39,
            'marginBottom' => 0.39,
            'marginLeft' => 0.39,
            'marginRight' => 0.39,
            'landscape' => false,
            'printBackground' => false,
            'scale' => 1.0,
        ];

        $formData = array_merge($defaults, $options);

        // Convert boolean values to strings
        foreach (['landscape', 'printBackground', 'omitBackground', 'preferCssPageSize', 
                 'generateDocumentOutline', 'generateTaggedPdf', 'singlePage'] as $boolField) {
            if (isset($formData[$boolField])) {
                $formData[$boolField] = $formData[$boolField] ? 'true' : 'false';
            }
        }

        return $formData;
    }

    /**
     * Build screenshot options
     */
    private function buildScreenshotOptions(array $options): array
    {
        $defaults = [
            'width' => 800,
            'height' => 600,
            'format' => 'png',
            'quality' => 100,
            'clip' => false,
            'omitBackground' => false,
            'optimizeForSpeed' => false,
        ];

        $formData = array_merge($defaults, $options);

        // Convert boolean values to strings
        foreach (['clip', 'omitBackground', 'optimizeForSpeed'] as $boolField) {
            if (isset($formData[$boolField])) {
                $formData[$boolField] = $formData[$boolField] ? 'true' : 'false';
            }
        }

        return $formData;
    }

    /**
     * Set page properties for A4 paper
     */
    public static function getA4Options(bool $landscape = false): array
    {
        return [
            'paperWidth' => $landscape ? 11.7 : 8.27,
            'paperHeight' => $landscape ? 8.27 : 11.7,
            'landscape' => $landscape,
        ];
    }

    /**
     * Set page properties for Letter paper
     */
    public static function getLetterOptions(bool $landscape = false): array
    {
        return [
            'paperWidth' => $landscape ? 11 : 8.5,
            'paperHeight' => $landscape ? 8.5 : 11,
            'landscape' => $landscape,
        ];
    }
}
