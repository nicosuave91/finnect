#!/bin/bash

# Finnect Development Setup Script
echo "🚀 Setting up Finnect development environment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create necessary directories
print_status "Creating project directories..."
mkdir -p logs/{frontend,backend,postgres,redis,kafka,opensearch,temporal}
mkdir -p data/{postgres,redis,opensearch}
mkdir -p ssl

# Set up environment files
print_status "Setting up environment files..."

# Backend .env
if [ ! -f backend/.env ]; then
    print_status "Creating backend .env file..."
    cp backend/.env.example backend/.env
    
    # Generate app key
    APP_KEY=$(openssl rand -base64 32)
    sed -i "s/APP_KEY=/APP_KEY=${APP_KEY}/" backend/.env
    
    # Generate encryption key
    ENCRYPTION_KEY=$(openssl rand -base64 32)
    sed -i "s/ENCRYPTION_KEY=/ENCRYPTION_KEY=${ENCRYPTION_KEY}/" backend/.env
    
    print_success "Backend .env file created"
else
    print_warning "Backend .env file already exists"
fi

# Frontend .env
if [ ! -f frontend/.env ]; then
    print_status "Creating frontend .env file..."
    cat > frontend/.env << EOF
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
NUXT_PUBLIC_SITE_URL=http://localhost:3000
NUXT_PUBLIC_APP_NAME=Finnect
NUXT_PUBLIC_APP_VERSION=1.0.0
NUXT_PUBLIC_ENVIRONMENT=development
EOF
    print_success "Frontend .env file created"
else
    print_warning "Frontend .env file already exists"
fi

# Set up SSL certificates for development
print_status "Setting up SSL certificates..."
if [ ! -f ssl/localhost.crt ] || [ ! -f ssl/localhost.key ]; then
    print_status "Generating self-signed SSL certificates..."
    openssl req -x509 -newkey rsa:4096 -keyout ssl/localhost.key -out ssl/localhost.crt -days 365 -nodes -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
    print_success "SSL certificates generated"
else
    print_warning "SSL certificates already exist"
fi

# Set up database initialization
print_status "Setting up database initialization..."
mkdir -p backend/database/init
cat > backend/database/init/01-init.sql << 'EOF'
-- Create tenant databases
CREATE DATABASE finnect_tenant_1;
CREATE DATABASE finnect_tenant_2;

-- Create extensions
\c finnect_tenant_1;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

\c finnect_tenant_2;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
EOF

# Set up Docker networks
print_status "Setting up Docker networks..."
docker network create finnect-network 2>/dev/null || print_warning "Docker network already exists"

# Build and start services
print_status "Building and starting services..."
docker-compose build

print_status "Starting database services..."
docker-compose up -d postgres redis kafka zookeeper opensearch temporal temporal-ui

# Wait for services to be ready
print_status "Waiting for services to be ready..."
sleep 30

# Run database migrations
print_status "Running database migrations..."
docker-compose exec backend php artisan migrate --force

# Seed database
print_status "Seeding database..."
docker-compose exec backend php artisan db:seed --force

# Install frontend dependencies
print_status "Installing frontend dependencies..."
cd frontend
npm install
cd ..

# Start all services
print_status "Starting all services..."
docker-compose up -d

# Wait for services to be ready
print_status "Waiting for all services to be ready..."
sleep 10

# Check service health
print_status "Checking service health..."

# Check backend
if curl -f http://localhost:8000/api/health > /dev/null 2>&1; then
    print_success "Backend is running at http://localhost:8000"
else
    print_warning "Backend may not be ready yet"
fi

# Check frontend
if curl -f http://localhost:3000 > /dev/null 2>&1; then
    print_success "Frontend is running at http://localhost:3000"
else
    print_warning "Frontend may not be ready yet"
fi

# Check Temporal UI
if curl -f http://localhost:8080 > /dev/null 2>&1; then
    print_success "Temporal UI is running at http://localhost:8080"
else
    print_warning "Temporal UI may not be ready yet"
fi

# Print summary
echo ""
print_success "🎉 Finnect development environment setup complete!"
echo ""
echo "📋 Service URLs:"
echo "   Frontend:     http://localhost:3000"
echo "   Backend API:  http://localhost:8000"
echo "   Temporal UI:  http://localhost:8080"
echo "   OpenSearch:   http://localhost:9200"
echo ""
echo "🔧 Development Commands:"
echo "   Start services:    docker-compose up -d"
echo "   Stop services:     docker-compose down"
echo "   View logs:         docker-compose logs -f"
echo "   Backend shell:     docker-compose exec backend bash"
echo "   Frontend dev:      cd frontend && npm run dev"
echo ""
echo "📚 Next Steps:"
echo "   1. Visit http://localhost:3000 to access the application"
echo "   2. Create your first tenant and user account"
echo "   3. Start developing your mortgage workflows"
echo ""
print_success "Happy coding! 🚀"