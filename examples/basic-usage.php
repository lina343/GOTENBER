<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GotenbergPHP\Client\GotenbergClient;
use GotenbergPHP\Client\ChromiumClient;
use GotenbergPHP\Client\LibreOfficeClient;
use GotenbergPHP\Client\PdfEngineClient;

// Initialize the Gotenberg client
$gotenbergClient = new GotenbergClient('http://localhost:3000');

// Example 1: Convert URL to PDF
echo "Converting URL to PDF...\n";
$chromiumClient = new ChromiumClient($gotenbergClient);

try {
    $pdfContent = $chromiumClient->convertUrlToPdf('https://example.com', [
        'paperWidth' => 8.27,  // A4 width
        'paperHeight' => 11.7, // A4 height
        'landscape' => false,
        'printBackground' => true,
    ]);
    
    file_put_contents(__DIR__ . '/output/example-url.pdf', $pdfContent);
    echo "✅ URL converted to PDF successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Example 2: Convert HTML to PDF
echo "\nConverting HTML to PDF...\n";
$html = '<!DOCTYPE html>
<html>
<head>
    <title>Sample PDF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: #007bff; }
        .highlight { background-color: yellow; padding: 5px; }
    </style>
</head>
<body>
    <h1>Hello from Gotenberg PHP!</h1>
    <p>This is a <span class="highlight">sample PDF</span> generated from HTML.</p>
    <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
</body>
</html>';

try {
    $pdfContent = $chromiumClient->convertHtmlToPdf($html);
    file_put_contents(__DIR__ . '/output/example-html.pdf', $pdfContent);
    echo "✅ HTML converted to PDF successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Example 3: Convert Office document to PDF (if you have a sample file)
echo "\nConverting Office document to PDF...\n";
$libreOfficeClient = new LibreOfficeClient($gotenbergClient);

$sampleDocPath = __DIR__ . '/sample-files/sample.docx';
if (file_exists($sampleDocPath)) {
    try {
        $pdfContent = $libreOfficeClient->convertDocumentsToPdf([$sampleDocPath], [
            'landscape' => false,
            'quality' => 90,
            'exportBookmarks' => true,
        ]);
        
        file_put_contents(__DIR__ . '/output/example-office.pdf', $pdfContent);
        echo "✅ Office document converted to PDF successfully!\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "ℹ️  No sample office document found at: $sampleDocPath\n";
}

// Example 4: Take a screenshot
echo "\nTaking website screenshot...\n";
try {
    $imageContent = $chromiumClient->screenshotUrl('https://github.com', [
        'format' => 'png',
        'width' => 1200,
        'height' => 800,
    ]);
    
    file_put_contents(__DIR__ . '/output/example-screenshot.png', $imageContent);
    echo "✅ Screenshot captured successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Example 5: Merge PDFs (if you have sample PDFs)
echo "\nMerging PDFs...\n";
$pdfEngineClient = new PdfEngineClient($gotenbergClient);

$pdf1Path = __DIR__ . '/output/example-url.pdf';
$pdf2Path = __DIR__ . '/output/example-html.pdf';

if (file_exists($pdf1Path) && file_exists($pdf2Path)) {
    try {
        $mergedPdfContent = $pdfEngineClient->mergePdfs([$pdf1Path, $pdf2Path]);
        file_put_contents(__DIR__ . '/output/example-merged.pdf', $mergedPdfContent);
        echo "✅ PDFs merged successfully!\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "ℹ️  Not enough PDF files to merge\n";
}

echo "\n🎉 All examples completed! Check the output folder for generated files.\n";
