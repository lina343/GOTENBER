#!/bin/bash

# Gotenberg PHP PDF Converter - Docker Startup Script

echo "🚀 Starting Gotenberg PHP PDF Converter..."

# Check if .env exists, if not copy from example
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
fi

# Create storage directories if they don't exist
echo "📁 Creating storage directories..."
mkdir -p storage/{uploads,temp,output,logs}

# Start Docker services
echo "🐳 Starting Docker services..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 10

# Check if Gotenberg is healthy
echo "🔍 Checking Gotenberg health..."
until curl -f http://localhost:3000/health > /dev/null 2>&1; do
    echo "   Waiting for Gotenberg to be ready..."
    sleep 5
done

echo "✅ Gotenberg is ready!"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
docker-compose exec -T php composer install

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📍 Access points:"
echo "   Web Interface: http://localhost:8080"
echo "   Gotenberg API: http://localhost:3000"
echo "   Health Check:  http://localhost:3000/health"
echo ""
echo "🔧 Useful commands:"
echo "   View logs:     docker-compose logs -f"
echo "   Stop services: docker-compose down"
echo "   Run tests:     docker-compose exec php composer test"
echo ""
