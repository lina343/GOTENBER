# 🚀 Gotenberg PHP PDF Converter

A comprehensive PHP library and web application for PDF conversion using the powerful [Gotenberg](https://gotenberg.dev/) API. Convert URLs, HTML, Office documents, and manipulate PDFs with ease through a beautiful web interface or programmatic API.

![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![Docker](https://img.shields.io/badge/Docker-Ready-blue)
![Gotenberg](https://img.shields.io/badge/Gotenberg-8.x-green)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### 🌐 **Web Conversions**
- **URL to PDF** - Convert any website to PDF using Chromium engine
- **HTML to PDF** - Convert custom HTML content with CSS styling
- **Website Screenshots** - Capture high-quality screenshots in PNG, JPEG, or WebP

### 📄 **Office Document Conversions**
- **70+ File Formats** - Word, Excel, PowerPoint, LibreOffice, and more
- **Quality Control** - High quality, normal, or compressed output
- **Batch Processing** - Convert and merge multiple documents
- **Format Support**: DOC, DOCX, XLS, XLSX, PPT, PPTX, ODT, ODS, ODP, RTF, TXT, CSV

### 🔧 **PDF Operations**
- **Merge PDFs** - Combine multiple PDF files into one document
- **Split PDFs** - Extract specific pages or ranges *(coming soon)*
- **Metadata Management** - Read and write PDF metadata *(coming soon)*
- **PDF/A Conversion** - Archive-compliant PDF formats *(coming soon)*

### 🎯 **Developer Features**
- **Modern PHP 8.1+** - Clean, typed, and well-documented code
- **Docker-First** - Complete containerized development environment
- **REST API Ready** - Programmatic access to all features
- **Comprehensive Testing** - PHPUnit tests and code quality tools
- **PSR Standards** - Follows PHP-FIG standards

## 🐳 Docker Setup

This project uses Docker for easy development and deployment. The setup includes:

- **Gotenberg Service** - PDF conversion API (port 3000)
- **PHP-FPM** - PHP application server
- **Nginx** - Web server (port 8080)
- **Redis** - Session storage and caching

### Quick Start

1. **Clone and setup:**
   ```bash
   git clone <repository-url>
   cd gotenberg-php
   cp .env.example .env
   ```

2. **Start the services:**
   ```bash
   docker-compose up -d
   ```

3. **Install dependencies:**
   ```bash
   docker-compose exec php composer install
   ```

4. **Access the application:**
   - Web Interface: http://localhost:8080
   - Gotenberg API: http://localhost:3000
   - API Documentation: http://localhost:8080/api/docs

### Docker Services

| Service | Port | Description |
|---------|------|-------------|
| nginx | 8080 | Web server for PHP application |
| gotenberg | 3000 | PDF conversion API service |
| php | 9000 | PHP-FPM application server |
| redis | 6379 | Redis for sessions and caching |

### Development Commands

```bash
# View logs
docker-compose logs -f

# Access PHP container
docker-compose exec php bash

# Run tests
docker-compose exec php composer test

# Check code quality
docker-compose exec php composer quality

# Stop services
docker-compose down
```

## 📁 Project Structure

```
gotenberg-php/
├── docker/              # Docker configuration
│   ├── nginx/           # Nginx configuration
│   └── php/             # PHP-FPM configuration
├── src/                 # PHP source code
│   ├── Client/          # Gotenberg API clients
│   ├── Services/        # Conversion services
│   ├── Web/             # Web interface & API
│   └── Config/          # Configuration management
├── tests/               # Unit & integration tests
├── examples/            # Usage examples
├── public/              # Web assets and entry point
├── storage/             # File storage
│   ├── uploads/         # Uploaded files
│   ├── temp/            # Temporary files
│   ├── output/          # Generated PDFs
│   └── logs/            # Application logs
└── config/              # Configuration files
```

## 🔧 Configuration

Copy `.env.example` to `.env` and adjust the settings:

- `GOTENBERG_URL` - Gotenberg service URL (default: http://gotenberg:3000)
- `UPLOAD_MAX_SIZE` - Maximum file upload size
- `DEFAULT_PAPER_SIZE` - Default PDF paper size (A4, Letter, etc.)
- `LOG_LEVEL` - Logging level (debug, info, warning, error)

## 📖 Usage Examples

Coming soon! Examples will be added as we build the core functionality.

## 🧪 Testing

```bash
# Run all tests
docker-compose exec php composer test

# Run with coverage
docker-compose exec php composer test-coverage

# Run static analysis
docker-compose exec php composer phpstan
```

## 📝 License

MIT License - see LICENSE file for details.
