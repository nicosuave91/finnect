# Finnect - Mortgage Broker-Dealer Platform

A comprehensive mortgage broker-dealer platform that orchestrates end-to-end loan workflows from application intake to investor delivery.

## Architecture Overview

### Frontend
- **Framework**: Vue 3 + Nuxt 3 with TypeScript
- **State Management**: Pinia + TanStack Query
- **UI Components**: Naive UI + custom component library
- **Styling**: Tailwind CSS
- **Features**: SSR, PWA, multi-tenant theming, real-time updates

### Backend
- **Primary Framework**: Laravel 11 (PHP 8.3)
- **Workflow Engine**: Temporal
- **Message Bus**: Apache Kafka
- **Database**: PostgreSQL 16 with Redis cache
- **Search**: OpenSearch

### Key Features
- Multi-tenant architecture with complete data isolation
- Regulatory compliance (TRID, ECOA, RESPA, GLBA, FCRA, AML/BSA, SAFE Act)
- Workflow orchestration with configurable business rules
- Integration ecosystem for 50+ mortgage industry vendors
- Scalable to 10,000+ concurrent users and 100,000+ loans annually per tenant

## Project Structure

```
finnect/
├── frontend/          # Vue 3 + Nuxt 3 application
├── backend/           # Laravel 11 API
├── shared/            # Shared types and schemas
├── infrastructure/    # Docker, Kubernetes, Terraform
├── docs/             # Documentation
└── scripts/          # Development and deployment scripts
```

## Getting Started

### Prerequisites
- Node.js 18+
- PHP 8.3+
- PostgreSQL 16
- Redis
- Docker (for development)

### Development Setup
1. Clone the repository
2. Run `./scripts/setup-dev.sh` to set up the development environment
3. Access the application at `http://localhost:3000`

## Compliance & Security
- Row-level security for multi-tenancy
- Encryption at rest and in transit
- Comprehensive audit trails
- Regulatory compliance controls
- SOC 2 Type II ready architecture