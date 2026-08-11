# API Specification

## Purpose
Cross-cutting REST API conventions for CityQuest backends and clients.

## Requirements

### Requirement: JSON Envelope
Public and private API success responses SHALL use a consistent JSON envelope with a `data` field and optional `meta` field.

#### Scenario: Single resource
- GIVEN a successful single-resource request
- WHEN the response is returned
- THEN the body contains `{ "data": <resource> }`

#### Scenario: Collection with metadata
- GIVEN a successful collection request with pagination or counts
- WHEN the response is returned
- THEN the body contains `{ "data": <items>, "meta": <metadata> }`

### Requirement: Error Responses
The system SHALL return appropriate HTTP status codes for client and auth failures (400, 401, 403, 404, 409, 422).

#### Scenario: Unauthorized
- GIVEN a protected endpoint without a valid session
- WHEN the request is made
- THEN the response status is 401

### Requirement: CORS with Credentials
The system SHALL allow the configured frontend origin to call the API with credentials (cookies).

#### Scenario: Credentialed browser request
- GIVEN a browser request from the allowed origin with credentials
- WHEN the API responds
- THEN CORS headers permit credentialed access

### Requirement: Cities Catalog
The system SHALL expose a cities list suitable for quest filters.

#### Scenario: Get cities
- GIVEN a client needs city filters
- WHEN the cities endpoint is requested
- THEN a list of city keys and display names is returned

### Requirement: Client Caching of Cities
Web clients SHALL cache the cities response locally with a TTL of one hour and MAY serve a stale cache if the network request fails.

#### Scenario: Cache hit within TTL
- GIVEN cities were fetched less than one hour ago
- WHEN the client needs cities again
- THEN the cached value is used without a network round-trip

#### Scenario: Network failure with stale cache
- GIVEN a cached cities payload that may be expired
- WHEN the network request fails
- THEN the client may return the stale cache to keep the UI usable
