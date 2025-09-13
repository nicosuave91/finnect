# Finnect

Finnect is a mortgage broker–dealer platform orchestrating the full loan lifecycle from borrower application through investor delivery.

## Project Structure
- `frontend/` – Nuxt 3 + TypeScript client
- `backend/` – Laravel 11 service
- `docs/` – architecture and API documentation
- `scripts/` – developer utilities
- `docker-compose.yml` – local development stack

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for a detailed architecture
overview, [docs/API.md](docs/API.md) and the [OpenAPI spec](docs/openapi.yaml)
for API conventions, and additional documents covering the
[OCR pipeline](docs/OCR_PIPELINE.md), [reporting](docs/REPORTING.md), and
[operations](docs/OPS.md).

## Development
Install dependencies:

```bash
# frontend
yarn --cwd frontend install

# backend
composer install --working-dir=backend
```

Project services can be started with `docker-compose up` once dependencies are installed.
