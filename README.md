# Gotenberg PHP PDF Converter

A comprehensive PHP library and web application for PDF conversion using the Gotenberg API. Convert URLs, HTML, Office documents, and manipulate PDFs with ease.

## 🚀 Features

- **URL to PDF** - Convert any web page to PDF using Chromium
- **HTML/Markdown to PDF** - Convert HTML and Markdown files to PDF
- **Office Documents to PDF** - Support for 70+ formats (Word, Excel, PowerPoint, etc.)
- **PDF Manipulation** - Merge, split, flatten, and manage PDF metadata
- **Web Interface** - Easy-to-use web interface for file uploads
- **REST API** - Programmatic access via REST endpoints
- **Docker Ready** - Complete Docker setup included

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
