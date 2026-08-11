# Delta for auth

> Historical delta migrated from Memory Bank archive `CQST-008`.
> Already reflected in `openspec/specs/auth/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: HttpOnly Cookie Session
The system MUST deliver the JWT in an HttpOnly cookie (not readable by client JavaScript).

#### Scenario: Authenticated API call
- GIVEN a client that logged in successfully
- WHEN the client calls a protected endpoint with credentials included
- THEN the server authenticates via the HttpOnly cookie

### Requirement: Current User Endpoint
The system SHALL expose an endpoint that returns the authenticated user's profile data from the server session.

#### Scenario: Authenticated me
- GIVEN a valid session cookie
- WHEN the client requests the current user
- THEN the server returns the user data

### Requirement: Security Headers
The system SHALL send HTTP security headers that mitigate XSS, clickjacking, and MIME sniffing.

#### Scenario: Responses include headers
- GIVEN a request to the web or API origin
- WHEN the response is returned
- THEN security headers are present
