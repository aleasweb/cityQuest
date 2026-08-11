# Delta for api

> Historical delta migrated from Memory Bank archive `REFACTORING-TEST-INFRA`.
> Already reflected in `openspec/specs/api/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Consistent Unauthorized Handling
Protected endpoints SHALL return a consistent 401 response when JWT is missing.

#### Scenario: Missing JWT
- GIVEN a protected endpoint without a session
- WHEN the request is made
- THEN the response status is 401
