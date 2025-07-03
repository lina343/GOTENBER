<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GotenbergPHP\Client\GotenbergClient;
use GotenbergPHP\Client\ChromiumClient;
use GotenbergPHP\Client\LibreOfficeClient;
use GotenbergPHP\Client\PdfEngineClient;
use GotenbergPHP\Exception\GotenbergException;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$gotenbergUrl = $_ENV['GOTENBERG_URL'] ?? 'http://gotenberg:3000';

// Initialize clients
$gotenbergClient = new GotenbergClient($gotenbergUrl);
$chromiumClient = new ChromiumClient($gotenbergClient);
$libreOfficeClient = new LibreOfficeClient($gotenbergClient);
$pdfEngineClient = new PdfEngineClient($gotenbergClient);

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'url_to_pdf':
                    $url = $_POST['url'] ?? '';
                    if (empty($url)) {
                        throw new \InvalidArgumentException('URL is required');
                    }
                    
                    $options = [];
                    if (isset($_POST['landscape']) && $_POST['landscape'] === 'on') {
                        $options['landscape'] = true;
                    }
                    if (isset($_POST['paper_size']) && $_POST['paper_size'] === 'a4') {
                        $options = array_merge($options, ChromiumClient::getA4Options($options['landscape'] ?? false));
                    }
                    
                    $pdfContent = $chromiumClient->convertUrlToPdf($url, $options);
                    
                    // Save PDF and provide download
                    $filename = 'url_' . date('Y-m-d_H-i-s') . '.pdf';
                    $filepath = __DIR__ . '/../storage/output/' . $filename;
                    file_put_contents($filepath, $pdfContent);
                    
                    // Trigger download
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . strlen($pdfContent));
                    echo $pdfContent;
                    exit;
                    
                case 'html_to_pdf':
                    $html = $_POST['html'] ?? '';
                    if (empty($html)) {
                        throw new \InvalidArgumentException('HTML content is required');
                    }

                    $pdfContent = $chromiumClient->convertHtmlToPdf($html);

                    // Save PDF and provide download
                    $filename = 'html_' . date('Y-m-d_H-i-s') . '.pdf';
                    $filepath = __DIR__ . '/../storage/output/' . $filename;
                    file_put_contents($filepath, $pdfContent);

                    // Trigger download
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . strlen($pdfContent));
                    echo $pdfContent;
                    exit;

                case 'office_to_pdf':
                    if (empty($_FILES['office_files']['tmp_name'][0])) {
                        throw new \InvalidArgumentException('At least one office file is required');
                    }

                    $uploadedFiles = [];
                    for ($i = 0; $i < count($_FILES['office_files']['tmp_name']); $i++) {
                        if ($_FILES['office_files']['error'][$i] === UPLOAD_ERR_OK) {
                            $uploadedFiles[] = [
                                'tmp_name' => $_FILES['office_files']['tmp_name'][$i],
                                'name' => $_FILES['office_files']['name'][$i]
                            ];
                        }
                    }

                    $options = [];
                    if (isset($_POST['office_landscape']) && $_POST['office_landscape'] === 'on') {
                        $options['landscape'] = true;
                    }
                    if (isset($_POST['office_merge']) && $_POST['office_merge'] === 'on') {
                        $options['merge'] = true;
                    }
                    if (isset($_POST['office_quality'])) {
                        $options = array_merge($options, $_POST['office_quality'] === 'high' ?
                            LibreOfficeClient::getHighQualityOptions() :
                            LibreOfficeClient::getCompressedOptions());
                    }

                    $pdfContent = $libreOfficeClient->convertUploadedDocumentsToPdf($uploadedFiles, $options);

                    // Save PDF and provide download
                    $filename = 'office_' . date('Y-m-d_H-i-s') . '.pdf';
                    $filepath = __DIR__ . '/../storage/output/' . $filename;
                    file_put_contents($filepath, $pdfContent);

                    // Trigger download
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . strlen($pdfContent));
                    echo $pdfContent;
                    exit;

                case 'merge_pdfs':
                    if (empty($_FILES['pdf_files']['tmp_name'][0])) {
                        throw new \InvalidArgumentException('At least two PDF files are required for merging');
                    }

                    // Save uploaded files temporarily
                    $tempFiles = [];
                    for ($i = 0; $i < count($_FILES['pdf_files']['tmp_name']); $i++) {
                        if ($_FILES['pdf_files']['error'][$i] === UPLOAD_ERR_OK) {
                            $tempPath = __DIR__ . '/../storage/temp/merge_' . uniqid() . '.pdf';
                            move_uploaded_file($_FILES['pdf_files']['tmp_name'][$i], $tempPath);
                            $tempFiles[] = $tempPath;
                        }
                    }

                    if (count($tempFiles) < 2) {
                        throw new \InvalidArgumentException('At least two valid PDF files are required');
                    }

                    $pdfContent = $pdfEngineClient->mergePdfs($tempFiles);

                    // Clean up temp files
                    foreach ($tempFiles as $tempFile) {
                        unlink($tempFile);
                    }

                    // Save PDF and provide download
                    $filename = 'merged_' . date('Y-m-d_H-i-s') . '.pdf';
                    $filepath = __DIR__ . '/../storage/output/' . $filename;
                    file_put_contents($filepath, $pdfContent);

                    // Trigger download
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . strlen($pdfContent));
                    echo $pdfContent;
                    exit;

                case 'screenshot_url':
                    $url = $_POST['screenshot_url'] ?? '';
                    if (empty($url)) {
                        throw new \InvalidArgumentException('URL is required');
                    }

                    $options = [
                        'format' => $_POST['screenshot_format'] ?? 'png',
                        'width' => (int)($_POST['screenshot_width'] ?? 1200),
                        'height' => (int)($_POST['screenshot_height'] ?? 800),
                    ];

                    $imageContent = $chromiumClient->screenshotUrl($url, $options);

                    // Save image and provide download
                    $extension = $options['format'];
                    $filename = 'screenshot_' . date('Y-m-d_H-i-s') . '.' . $extension;
                    $filepath = __DIR__ . '/../storage/output/' . $filename;
                    file_put_contents($filepath, $imageContent);

                    // Trigger download
                    $mimeType = $extension === 'png' ? 'image/png' : ($extension === 'jpeg' ? 'image/jpeg' : 'image/webp');
                    header('Content-Type: ' . $mimeType);
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . strlen($imageContent));
                    echo $imageContent;
                    exit;
            }
        }
    } catch (GotenbergException $e) {
        $error = 'Conversion failed: ' . $e->getMessage();
    } catch (\Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Check Gotenberg health
$isHealthy = false;
$version = 'Unknown';
try {
    $isHealthy = $gotenbergClient->isHealthy();
    if ($isHealthy) {
        $version = $gotenbergClient->getVersion();
    }
} catch (\Exception $e) {
    $error = 'Cannot connect to Gotenberg: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gotenberg PHP PDF Converter</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .status {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .status.healthy {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.unhealthy {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-section h3 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="url"], input[type="number"], input[type="file"], textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="file"] {
            padding: 8px;
            background-color: #f8f9fa;
        }
        small {
            color: #666;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        textarea {
            height: 150px;
            resize: vertical;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Gotenberg PHP PDF Converter</h1>
            <p>Convert URLs, HTML, and documents to PDF using Gotenberg API</p>
        </div>

        <!-- Gotenberg Status -->
        <div class="status <?= $isHealthy ? 'healthy' : 'unhealthy' ?>">
            <?php if ($isHealthy): ?>
                ✅ Gotenberg is running (Version: <?= htmlspecialchars($version) ?>)
            <?php else: ?>
                ❌ Gotenberg is not available
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- URL to PDF Form -->
        <div class="form-section">
            <h3>🌐 Convert URL to PDF</h3>
            <form method="POST">
                <input type="hidden" name="action" value="url_to_pdf">
                <div class="form-group">
                    <label for="url">Website URL:</label>
                    <input type="url" id="url" name="url" placeholder="https://example.com" required>
                </div>
                <div class="form-group">
                    <label for="paper_size">Paper Size:</label>
                    <select id="paper_size" name="paper_size">
                        <option value="letter">Letter (8.5 x 11)</option>
                        <option value="a4">A4 (8.27 x 11.7)</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="landscape" name="landscape">
                        <label for="landscape">Landscape orientation</label>
                    </div>
                </div>
                <button type="submit">Convert URL to PDF</button>
            </form>
        </div>

        <!-- HTML to PDF Form -->
        <div class="form-section">
            <h3>📝 Convert HTML to PDF</h3>
            <form method="POST">
                <input type="hidden" name="action" value="html_to_pdf">
                <div class="form-group">
                    <label for="html">HTML Content:</label>
                    <textarea id="html" name="html" placeholder="<!DOCTYPE html>
<html>
<head>
    <title>My PDF</title>
</head>
<body>
    <h1>Hello World!</h1>
    <p>This will be converted to PDF.</p>
</body>
</html>" required></textarea>
                </div>
                <button type="submit">Convert HTML to PDF</button>
            </form>
        </div>

        <!-- Office Documents to PDF Form -->
        <div class="form-section">
            <h3>📄 Convert Office Documents to PDF</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="office_to_pdf">
                <div class="form-group">
                    <label for="office_files">Office Files (Word, Excel, PowerPoint, etc.):</label>
                    <input type="file" id="office_files" name="office_files[]" multiple accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp,.rtf,.txt,.csv" required>
                    <small>Supported: DOC, DOCX, XLS, XLSX, PPT, PPTX, ODT, ODS, ODP, RTF, TXT, CSV</small>
                </div>
                <div class="form-group">
                    <label for="office_quality">Quality:</label>
                    <select id="office_quality" name="office_quality">
                        <option value="normal">Normal Quality</option>
                        <option value="high">High Quality</option>
                        <option value="compressed">Compressed</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="office_landscape" name="office_landscape">
                        <label for="office_landscape">Landscape orientation</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="office_merge" name="office_merge">
                        <label for="office_merge">Merge multiple files into one PDF</label>
                    </div>
                </div>
                <button type="submit">Convert to PDF</button>
            </form>
        </div>

        <!-- PDF Merge Form -->
        <div class="form-section">
            <h3>🔗 Merge PDF Files</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="merge_pdfs">
                <div class="form-group">
                    <label for="pdf_files">PDF Files to Merge:</label>
                    <input type="file" id="pdf_files" name="pdf_files[]" multiple accept=".pdf" required>
                    <small>Select 2 or more PDF files to merge them into one document</small>
                </div>
                <button type="submit">Merge PDFs</button>
            </form>
        </div>

        <!-- Screenshot Form -->
        <div class="form-section">
            <h3>📷 Take Website Screenshot</h3>
            <form method="POST">
                <input type="hidden" name="action" value="screenshot_url">
                <div class="form-group">
                    <label for="screenshot_url">Website URL:</label>
                    <input type="url" id="screenshot_url" name="screenshot_url" placeholder="https://example.com" required>
                </div>
                <div class="form-group">
                    <label for="screenshot_format">Image Format:</label>
                    <select id="screenshot_format" name="screenshot_format">
                        <option value="png">PNG</option>
                        <option value="jpeg">JPEG</option>
                        <option value="webp">WebP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="screenshot_width">Width (pixels):</label>
                    <input type="number" id="screenshot_width" name="screenshot_width" value="1200" min="100" max="3000">
                </div>
                <div class="form-group">
                    <label for="screenshot_height">Height (pixels):</label>
                    <input type="number" id="screenshot_height" name="screenshot_height" value="800" min="100" max="3000">
                </div>
                <button type="submit">Take Screenshot</button>
            </form>
        </div>

        <!-- Info Section -->
        <div class="form-section">
            <h3>ℹ️ Information</h3>
            <p><strong>Gotenberg URL:</strong> <?= htmlspecialchars($gotenbergUrl) ?></p>
            <p><strong>Supported conversions:</strong></p>
            <ul>
                <li><strong>🌐 URL to PDF</strong> - Convert any website to PDF using Chromium</li>
                <li><strong>📝 HTML to PDF</strong> - Convert custom HTML content to PDF</li>
                <li><strong>📄 Office Documents to PDF</strong> - Convert Word, Excel, PowerPoint, and 70+ formats</li>
                <li><strong>🔗 PDF Operations</strong> - Merge multiple PDFs into one document</li>
                <li><strong>📷 Website Screenshots</strong> - Capture website screenshots in PNG, JPEG, or WebP</li>
            </ul>
            <p><strong>Supported Office Formats:</strong></p>
            <ul>
                <li><strong>Documents:</strong> DOC, DOCX, ODT, RTF, TXT</li>
                <li><strong>Spreadsheets:</strong> XLS, XLSX, ODS, CSV</li>
                <li><strong>Presentations:</strong> PPT, PPTX, ODP</li>
                <li><strong>Other:</strong> PDF, HTML, EPUB, Pages, Numbers, Key</li>
            </ul>
            <p><strong>Coming Soon:</strong></p>
            <ul>
                <li>PDF Split & Extract pages</li>
                <li>PDF Metadata editing</li>
                <li>PDF/A conversion for archival</li>
                <li>Markdown to PDF conversion</li>
                <li>Batch processing</li>
            </ul>
        </div>
    </div>
</body>
</html>
