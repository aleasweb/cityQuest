# Delta for api

> Historical delta migrated from Memory Bank archive `CQST-007-partial`.
> Already reflected in `openspec/specs/api/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Cities Catalog
The system SHALL expose a cities list suitable for quest filters with display names.

#### Scenario: Get cities
- GIVEN a client needs city filters
- WHEN the cities endpoint is requested
- THEN city keys and display names are returned
