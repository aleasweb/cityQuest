# Delta for api

> Historical delta migrated from Memory Bank archive `CQST-007-phase1`.
> Already reflected in `openspec/specs/api/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: CORS with Credentials
The system SHALL allow the configured frontend origin to call the API (CORS).

#### Scenario: Browser cross-origin request
- GIVEN a browser request from the allowed origin
- WHEN the API responds
- THEN CORS headers permit access
