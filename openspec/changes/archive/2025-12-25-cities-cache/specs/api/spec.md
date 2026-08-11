# Delta for api

> Historical delta migrated from Memory Bank archive `CQST-009`.
> Already reflected in `openspec/specs/api/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Client Caching of Cities
Web clients SHALL cache the cities response locally with a TTL of one hour and MAY serve a stale cache if the network request fails.

#### Scenario: Cache hit within TTL
- GIVEN cities were fetched less than one hour ago
- WHEN the client needs cities again
- THEN the cached value is used without a network round-trip
