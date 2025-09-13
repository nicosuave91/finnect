# Operational Excellence

Finnect's platform reliability plan includes:

- **Monitoring** – Prometheus scrapes metrics from services and Grafana
dashboards visualize health and compliance KPIs.
- **Disaster Recovery** – docker-compose includes Grafana for observing backup
jobs and replica status. Nightly backups of PostgreSQL and OpenSearch are
stored off-site.
- **Dependency Management** – previous installation attempts showed missing
Composer and NPM packages which prevented tests from running. Resolving these
dependencies is required before production deployment.
