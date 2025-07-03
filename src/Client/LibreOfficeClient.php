<?php

declare(strict_types=1);

namespace GotenbergPHP\Client;

use GotenbergPHP\Exception\ConversionException;

/**
 * Client for LibreOffice-based document conversions
 */
class LibreOfficeClient
{
    private GotenbergClient $client;

    public function __construct(GotenbergClient $client)
    {
        $this->client = $client;
    }

    /**
     * Convert office documents to PDF
     */
    public function convertDocumentsToPdf(array $filePaths, array $options = []): string
    {
        if (empty($filePaths)) {
            throw new ConversionException('At least one file is required');
        }

        // Validate files exist
        foreach ($filePaths as $filePath) {
            if (!file_exists($filePath)) {
                throw new ConversionException("File not found: {$filePath}");
            }
        }

        $files = [];
        foreach ($filePaths as $filePath) {
            $files['files'][] = $filePath;
        }

        $formData = $this->buildLibreOfficeOptions($options);
        
        $response = $this->client->sendMultipartRequest('/forms/libreoffice/convert', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Convert office documents from uploaded files
     */
    public function convertUploadedDocumentsToPdf(array $uploadedFiles, array $options = []): string
    {
        if (empty($uploadedFiles)) {
            throw new ConversionException('At least one file is required');
        }

        $files = [];
        foreach ($uploadedFiles as $uploadedFile) {
            if (isset($uploadedFile['tmp_name']) && isset($uploadedFile['name'])) {
                $files['files'][] = [
                    'content' => file_get_contents($uploadedFile['tmp_name']),
                    'filename' => $uploadedFile['name']
                ];
            }
        }

        $formData = $this->buildLibreOfficeOptions($options);
        
        $response = $this->client->sendMultipartRequest('/forms/libreoffice/convert', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Build LibreOffice conversion options
     */
    private function buildLibreOfficeOptions(array $options): array
    {
        $defaults = [
            'landscape' => false,
            'merge' => false,
            'pdfa' => '',
            'pdfua' => false,
            'exportFormFields' => true,
            'allowDuplicateFieldNames' => false,
            'exportBookmarks' => true,
            'exportBookmarksToPdfDestination' => false,
            'exportPlaceholders' => false,
            'exportNotes' => false,
            'exportNotesPages' => false,
            'exportOnlyNotesPages' => false,
            'exportNotesInMargin' => false,
            'convertOooTargetToPdfTarget' => false,
            'exportLinksRelativeFsys' => false,
            'exportHiddenSlides' => false,
            'skipEmptyPages' => false,
            'addOriginalDocumentAsStream' => false,
            'singlePageSheets' => false,
            'losslessImageCompression' => false,
            'quality' => 90,
            'reduceImageResolution' => false,
            'maxImageResolution' => 300,
        ];

        $formData = array_merge($defaults, $options);

        // Convert boolean values to strings
        $boolFields = [
            'landscape', 'merge', 'pdfua', 'exportFormFields', 'allowDuplicateFieldNames',
            'exportBookmarks', 'exportBookmarksToPdfDestination', 'exportPlaceholders',
            'exportNotes', 'exportNotesPages', 'exportOnlyNotesPages', 'exportNotesInMargin',
            'convertOooTargetToPdfTarget', 'exportLinksRelativeFsys', 'exportHiddenSlides',
            'skipEmptyPages', 'addOriginalDocumentAsStream', 'singlePageSheets',
            'losslessImageCompression', 'reduceImageResolution'
        ];

        foreach ($boolFields as $field) {
            if (isset($formData[$field])) {
                $formData[$field] = $formData[$field] ? 'true' : 'false';
            }
        }

        // Remove empty PDF/A format
        if (empty($formData['pdfa'])) {
            unset($formData['pdfa']);
        }

        return $formData;
    }

    /**
     * Get supported file extensions
     */
    public static function getSupportedExtensions(): array
    {
        return [
            // Document formats
            'doc', 'docx', 'docm', 'dot', 'dotx', 'dotm', 'odt', 'ott', 'rtf', 'txt',
            // Spreadsheet formats
            'xls', 'xlsx', 'xlsm', 'xlsb', 'xlt', 'xltx', 'xltm', 'ods', 'ots', 'csv',
            // Presentation formats
            'ppt', 'pptx', 'pptm', 'pot', 'potx', 'potm', 'pps', 'ppsx', 'odp', 'otp',
            // Other formats
            'pdf', 'html', 'htm', 'epub', 'pages', 'numbers', 'key'
        ];
    }

    /**
     * Check if file extension is supported
     */
    public static function isExtensionSupported(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, self::getSupportedExtensions());
    }

    /**
     * Get options for high quality conversion
     */
    public static function getHighQualityOptions(): array
    {
        return [
            'quality' => 100,
            'losslessImageCompression' => true,
            'reduceImageResolution' => false,
            'exportFormFields' => true,
            'exportBookmarks' => true,
        ];
    }

    /**
     * Get options for compressed conversion
     */
    public static function getCompressedOptions(): array
    {
        return [
            'quality' => 50,
            'losslessImageCompression' => false,
            'reduceImageResolution' => true,
            'maxImageResolution' => 150,
        ];
    }
}
