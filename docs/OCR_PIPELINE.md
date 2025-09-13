# Document Processing & OCR Pipeline

Finnect ingests borrower documents and routes them through an OCR and AI
pipeline:

1. **Upload** – documents are stored in object storage and a processing job is
enqueued.
2. **Pre-processing** – images are normalized and converted to searchable PDF.
3. **Text extraction** – Tesseract and layout-aware models extract structured
   data.
4. **Classification** – machine learning models tag document types and detect
   missing pages.
5. **Validation** – extracted fields are validated against loan data and
   regulatory rules.
6. **Delivery** – results are persisted to PostgreSQL and indexed in OpenSearch
   for audit and retrieval.

The pipeline is orchestrated via Temporal workflows and publishes events to
Kafka for downstream consumers.
