# Finnect API Documentation

## Overview

The Finnect API provides comprehensive endpoints for managing mortgage loans, borrowers, compliance, workflows, and integrations. The API follows RESTful principles and supports multi-tenant architecture.

An OpenAPI 3.0 specification is available at [`docs/openapi.yaml`](openapi.yaml)
for tooling and client generation.

## Base URL

```
Production: https://api.finnect.com/v1
Development: http://localhost:8000/api
```

## Authentication

All API requests require authentication using Bearer tokens.

```http
Authorization: Bearer <your-token>
X-Tenant-ID: <tenant-id>
```

## Response Format

All API responses follow a consistent format:

```json
{
  "data": {},
  "message": "Success message",
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

## Error Handling

Errors are returned with appropriate HTTP status codes:

```json
{
  "error": "Error message",
  "errors": {
    "field": ["Validation error message"]
  },
  "code": "ERROR_CODE"
}
```

## Endpoints

### Authentication

#### POST /auth/login
Authenticate user and return access token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```

**Response:**
```json
{
  "data": {
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "user@example.com",
      "roles": ["loan_officer"]
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

#### POST /auth/register
Register new user account.

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "nmls_id": "123456"
}
```

#### POST /auth/logout
Logout user and invalidate token.

#### GET /auth/me
Get current user information.

### Loans

#### GET /loans
Get paginated list of loans.

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page
- `status` (string): Filter by status
- `loan_officer_id` (integer): Filter by loan officer
- `date_from` (date): Filter from date
- `date_to` (date): Filter to date
- `search` (string): Search term

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "loan_number": "LN2024010001",
      "status": "processing",
      "loan_amount": 500000.00,
      "interest_rate": 6.5,
      "loan_type": "conventional",
      "property_type": "single_family",
      "application_date": "2024-01-15",
      "borrower": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com"
      },
      "loan_officer": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

#### POST /loans
Create new loan.

**Request:**
```json
{
  "borrower_id": 1,
  "co_borrower_id": 2,
  "loan_amount": 500000.00,
  "interest_rate": 6.5,
  "loan_type": "conventional",
  "property_type": "single_family",
  "occupancy_type": "primary",
  "purpose": "purchase",
  "application_date": "2024-01-15",
  "loan_data": {
    "property_address": "123 Main St, City, State 12345"
  }
}
```

#### GET /loans/{id}
Get specific loan details.

#### PUT /loans/{id}
Update loan information.

#### DELETE /loans/{id}
Delete loan (only if in application status).

#### POST /loans/{id}/status
Update loan status.

**Request:**
```json
{
  "status": "approved",
  "reason": "All requirements met"
}
```

#### POST /loans/{id}/compliance-check
Run compliance check for loan.

#### GET /loans/{id}/audit-trail
Get audit trail for loan.

#### GET /loans/{id}/workflow
Get workflow steps for loan.

### Borrowers

#### GET /borrowers
Get paginated list of borrowers.

#### POST /borrowers
Create new borrower.

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "ssn": "123-45-6789",
  "date_of_birth": "1990-01-01",
  "marital_status": "single",
  "address_data": {
    "street_address": "123 Main St",
    "city": "City",
    "state": "State",
    "zip_code": "12345",
    "country": "US"
  },
  "employment_data": {
    "employer_name": "ABC Company",
    "job_title": "Software Engineer",
    "employment_type": "full_time",
    "annual_income": 75000
  }
}
```

#### GET /borrowers/{id}
Get specific borrower details.

#### PUT /borrowers/{id}
Update borrower information.

#### DELETE /borrowers/{id}
Delete borrower.

#### POST /borrowers/{id}/verify-identity
Verify borrower identity.

#### GET /borrowers/{id}/credit-report
Get borrower credit report.

### Compliance

#### GET /compliance/regulations
Get all supported regulations.

**Response:**
```json
{
  "data": [
    {
      "code": "TRID",
      "name": "TRID (TILA-RESPA Integrated Disclosure)",
      "description": "Truth in Lending Act and Real Estate Settlement Procedures Act",
      "requirements": {
        "loan_estimate": "Loan Estimate must be provided within 3 business days",
        "closing_disclosure": "Closing Disclosure must be provided 3 business days before closing"
      }
    }
  ]
}
```

#### GET /compliance/violations
Get compliance violations.

**Query Parameters:**
- `regulation` (string): Filter by regulation
- `severity` (string): Filter by severity
- `date_from` (date): Filter from date
- `date_to` (date): Filter to date

#### GET /compliance/audit-trail
Get compliance audit trail.

#### POST /compliance/reports
Generate compliance report.

**Request:**
```json
{
  "report_type": "summary",
  "date_from": "2024-01-01",
  "date_to": "2024-01-31",
  "regulations": ["TRID", "ECOA"],
  "format": "pdf"
}
```

#### GET /compliance/reports/{id}/download
Download generated report.

### Workflows

#### GET /workflows
Get all workflows.

#### POST /workflows
Create new workflow.

#### GET /workflows/{id}
Get specific workflow.

#### PUT /workflows/{id}
Update workflow.

#### DELETE /workflows/{id}
Delete workflow.

#### POST /workflows/{id}/steps
Add step to workflow.

#### PUT /workflows/{id}/steps/{step_id}
Update workflow step.

#### DELETE /workflows/{id}/steps/{step_id}
Remove workflow step.

### Documents

#### GET /documents
Get paginated list of documents.

#### POST /documents/upload
Upload document.

**Request:**
```multipart/form-data
file: <file>
loan_id: 1
category: "income_verification"
type: "bank_statement"
```

#### GET /documents/{id}
Get document details.

#### PUT /documents/{id}
Update document metadata.

#### DELETE /documents/{id}
Delete document.

#### GET /documents/{id}/download
Download document.

#### POST /documents/{id}/verify
Verify document authenticity.

### Integrations

Examples include credit bureaus, Plaid and Truework for VOA/VOE, AUS systems
like Desktop Underwriter, Loan Prospector, FHA TOTAL, and closing services such
as Snapdocs.

#### GET /integrations
Get all integrations.

#### GET /integrations/{id}/status
Get integration status.

#### POST /integrations/{id}/sync
Sync data with integration.

#### GET /integrations/{id}/logs
Get integration logs.

### Dashboard

#### GET /dashboard/stats
Get dashboard statistics.

**Response:**
```json
{
  "data": {
    "total_loans": 150,
    "active_loans": 45,
    "completed_loans": 100,
    "total_loan_amount": 75000000.00,
    "compliance_violations": 3,
    "pending_approvals": 12,
    "overdue_tasks": 5
  }
}
```

#### GET /dashboard/loans/summary
Get loan summary statistics.

#### GET /dashboard/compliance/summary
Get compliance summary.

#### GET /dashboard/workflow/summary
Get workflow summary.

## Rate Limiting

API requests are rate limited to prevent abuse:

- **Authenticated users**: 1000 requests per hour
- **Unauthenticated users**: 100 requests per hour
- **Bulk operations**: 100 requests per hour

Rate limit headers are included in responses:

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## Webhooks

Finnect supports webhooks for real-time notifications:

### Loan Status Changed
```json
{
  "event": "loan.status_changed",
  "data": {
    "loan_id": 1,
    "old_status": "processing",
    "new_status": "approved",
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### Compliance Violation
```json
{
  "event": "compliance.violation",
  "data": {
    "loan_id": 1,
    "regulation": "TRID",
    "violation_type": "missing_disclosure",
    "severity": "high",
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### Workflow Step Completed
```json
{
  "event": "workflow.step_completed",
  "data": {
    "loan_id": 1,
    "step_name": "Credit Check",
    "completed_by": 2,
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

## SDKs

Official SDKs are available for:

- **JavaScript/TypeScript**: `npm install @finnect/sdk`
- **Python**: `pip install finnect-sdk`
- **PHP**: `composer require finnect/sdk`
- **Java**: Available in Maven Central

## Support

For API support and questions:

- **Documentation**: https://docs.finnect.com
- **Support Email**: api-support@finnect.com
- **Status Page**: https://status.finnect.com
- **GitHub**: https://github.com/finnect/api-examples