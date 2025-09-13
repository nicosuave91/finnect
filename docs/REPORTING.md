# Reporting & Data Warehouse

Finnect exposes operational reporting and analytics via a dedicated data
warehouse tier:

- **ETL** – nightly jobs extract data from PostgreSQL and load it into a star
  schema optimized for analytics.
- **Storage** – data is persisted in an analytical warehouse (e.g. BigQuery or
  Snowflake) with partitioning by tenant and time.
- **Access** – Looker dashboards and ad-hoc SQL queries provide insight into
  loan pipelines, compliance metrics, and integration performance.
- **Audit** – all ETL jobs log success and failure events to Kafka and are
  surfaced through Grafana dashboards.

Read replicas of PostgreSQL support low-latency reporting workloads while
keeping transactional performance isolated.
