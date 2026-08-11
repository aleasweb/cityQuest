# Delta for api

> Historical delta migrated from Memory Bank archive `CQST-007-phase2`.
> Already reflected in `openspec/specs/api/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: JSON Envelope
API success responses SHALL use a consistent JSON envelope with a `data` field and optional `meta` field.

#### Scenario: Single resource
- GIVEN a successful single-resource request
- WHEN the response is returned
- THEN the body contains `{ "data": <resource> }`
