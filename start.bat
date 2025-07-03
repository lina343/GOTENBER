@echo off
echo 🚀 Starting Gotenberg PHP PDF Converter...

REM Check if .env exists, if not copy from example
if not exist .env (
    echo 📝 Creating .env file from .env.example...
    copy .env.example .env
)

REM Create storage directories if they don't exist
echo 📁 Creating storage directories...
if not exist storage\uploads mkdir storage\uploads
if not exist storage\temp mkdir storage\temp
if not exist storage\output mkdir storage\output
if not exist storage\logs mkdir storage\logs

REM Start Docker services
echo 🐳 Starting Docker services...
docker-compose up -d

REM Wait for services to be ready
echo ⏳ Waiting for services to start...
timeout /t 10 /nobreak > nul

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
docker-compose exec -T php composer install

echo.
echo 🎉 Setup complete!
echo.
echo 📍 Access points:
echo    Web Interface: http://localhost:8080
echo    Gotenberg API: http://localhost:3000
echo    Health Check:  http://localhost:3000/health
echo.
echo 🔧 Useful commands:
echo    View logs:     docker-compose logs -f
echo    Stop services: docker-compose down
echo    Run tests:     docker-compose exec php composer test
echo.
pause
