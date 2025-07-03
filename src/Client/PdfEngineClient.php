<?php

declare(strict_types=1);

namespace GotenbergPHP\Client;

use GotenbergPHP\Exception\ConversionException;

/**
 * Client for PDF manipulation operations
 */
class PdfEngineClient
{
    private GotenbergClient $client;

    public function __construct(GotenbergClient $client)
    {
        $this->client = $client;
    }

    /**
     * Merge multiple PDF files
     */
    public function mergePdfs(array $pdfPaths, array $options = []): string
    {
        if (count($pdfPaths) < 2) {
            throw new ConversionException('At least two PDF files are required for merging');
        }

        // Validate files exist and are PDFs
        foreach ($pdfPaths as $pdfPath) {
            if (!file_exists($pdfPath)) {
                throw new ConversionException("PDF file not found: {$pdfPath}");
            }
            if (strtolower(pathinfo($pdfPath, PATHINFO_EXTENSION)) !== 'pdf') {
                throw new ConversionException("File is not a PDF: {$pdfPath}");
            }
        }

        $files = [];
        foreach ($pdfPaths as $pdfPath) {
            $files['files'][] = $pdfPath;
        }

        $formData = $this->buildPdfEngineOptions($options);
        
        $response = $this->client->sendMultipartRequest('/forms/pdfengines/merge', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Split a PDF file
     */
    public function splitPdf(string $pdfPath, string $mode, string $span, array $options = []): string
    {
        if (!file_exists($pdfPath)) {
            throw new ConversionException("PDF file not found: {$pdfPath}");
        }

        if (!in_array($mode, ['intervals', 'pages'])) {
            throw new ConversionException("Split mode must be 'intervals' or 'pages'");
        }

        $files = ['files' => $pdfPath];
        
        $formData = array_merge([
            'splitMode' => $mode,
            'splitSpan' => $span,
        ], $this->buildPdfEngineOptions($options));
        
        $response = $this->client->sendMultipartRequest('/forms/pdfengines/split', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Flatten PDF forms
     */
    public function flattenPdf(string $pdfPath): string
    {
        if (!file_exists($pdfPath)) {
            throw new ConversionException("PDF file not found: {$pdfPath}");
        }

        $files = ['files' => $pdfPath];
        
        $response = $this->client->sendMultipartRequest('/forms/pdfengines/flatten', [], $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Read PDF metadata
     */
    public function readPdfMetadata(array $pdfPaths): array
    {
        if (empty($pdfPaths)) {
            throw new ConversionException('At least one PDF file is required');
        }

        // Validate files exist and are PDFs
        foreach ($pdfPaths as $pdfPath) {
            if (!file_exists($pdfPath)) {
                throw new ConversionException("PDF file not found: {$pdfPath}");
            }
        }

        $files = [];
        foreach ($pdfPaths as $pdfPath) {
            $files['files'][] = $pdfPath;
        }
        
        $response = $this->client->sendMultipartRequest('/forms/pdfengines/metadata/read', [], $files);
        
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Write PDF metadata
     */
    public function writePdfMetadata(array $pdfPaths, array $metadata): string
    {
        if (empty($pdfPaths)) {
            throw new ConversionException('At least one PDF file is required');
        }

        if (empty($metadata)) {
            throw new ConversionException('Metadata is required');
        }

        // Validate files exist and are PDFs
        foreach ($pdfPaths as $pdfPath) {
            if (!file_exists($pdfPath)) {
                throw new ConversionException("PDF file not found: {$pdfPath}");
            }
        }

        $files = [];
        foreach ($pdfPaths as $pdfPath) {
            $files['files'][] = $pdfPath;
        }

        $formData = ['metadata' => json_encode($metadata)];
        
        $response = $this->client->sendMultipartRequest('/forms/pdfengines/metadata/write', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Convert PDFs to PDF/A format
     */
    public function convertToPdfA(array $pdfPaths, string $pdfaFormat, bool $pdfua = false): string
    {
        if (empty($pdfPaths)) {
            throw new ConversionException('At least one PDF file is required');
        }

        if (!in_array($pdfaFormat, ['PDF/A-1b', 'PDF/A-2b', 'PDF/A-3b'])) {
            throw new ConversionException('Invalid PDF/A format. Must be PDF/A-1b, PDF/A-2b, or PDF/A-3b');
        }

        // Validate files exist and are PDFs
        foreach ($pdfPaths as $pdfPath) {
            if (!file_exists($pdfPath)) {
                throw new ConversionException("PDF file not found: {$pdfPath}");
            }
        }

        $files = [];
        foreach ($pdfPaths as $pdfPath) {
            $files['files'][] = $pdfPath;
        }

        $formData = [
            'pdfa' => $pdfaFormat,
            'pdfua' => $pdfua ? 'true' : 'false',
        ];
        
        $response = $this->client->sendMultipartRequest('/forms/pdfengines/convert', $formData, $files);
        
        return $response->getBody()->getContents();
    }

    /**
     * Build PDF engine options
     */
    private function buildPdfEngineOptions(array $options): array
    {
        $defaults = [
            'pdfa' => '',
            'pdfua' => false,
            'flatten' => false,
            'splitUnify' => false,
        ];

        $formData = array_merge($defaults, $options);

        // Convert boolean values to strings
        foreach (['pdfua', 'flatten', 'splitUnify'] as $boolField) {
            if (isset($formData[$boolField])) {
                $formData[$boolField] = $formData[$boolField] ? 'true' : 'false';
            }
        }

        // Remove empty PDF/A format
        if (empty($formData['pdfa'])) {
            unset($formData['pdfa']);
        }

        // Handle metadata
        if (isset($formData['metadata']) && is_array($formData['metadata'])) {
            $formData['metadata'] = json_encode($formData['metadata']);
        }

        return $formData;
    }

    /**
     * Get available PDF/A formats
     */
    public static function getPdfAFormats(): array
    {
        return ['PDF/A-1b', 'PDF/A-2b', 'PDF/A-3b'];
    }

    /**
     * Get common metadata fields
     */
    public static function getCommonMetadataFields(): array
    {
        return [
            'Title' => 'Document title',
            'Author' => 'Document author',
            'Subject' => 'Document subject',
            'Keywords' => 'Document keywords (array)',
            'Creator' => 'Application that created the document',
            'Producer' => 'Application that produced the PDF',
            'CreationDate' => 'Creation date (ISO 8601 format)',
            'ModDate' => 'Modification date (ISO 8601 format)',
            'Trapped' => 'Trapping information',
        ];
    }
}
