# EventDNA 3-Tier Architecture

## 1. Presentation Tier
- UI pages and page-specific styles under [presentation](presentation)
- User-facing routes are grouped by domain:
  - [presentation/admin](presentation/admin)
  - [presentation/attendees](presentation/attendees)
  - [presentation/community](presentation/community)
  - [presentation/events](presentation/events)
- Shared presentation assets remain in [shared](shared) and are referenced by the pages.

## 2. Application Tier
- Business logic and front-end interaction helpers live under [application](application)
- Current shell behavior is centralized in [application/shell.js](application/shell.js)
- Future services, utilities, and controllers should be added here.

## 3. Data Tier
- Database schema and data definitions live under [data](data)
- The SQL schema is stored in [data/db/schema/eventdna_schema.sql](data/db/schema/eventdna_schema.sql)
- Use [data/mocks](data/mocks) for seed or sample data.
