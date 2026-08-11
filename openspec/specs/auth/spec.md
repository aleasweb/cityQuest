# Auth Specification

## Purpose
Authentication and session management for CityQuest API clients (Web and future mobile).

## Requirements

### Requirement: User Registration
The system SHALL allow a new user to register with username, email, and password.

#### Scenario: Successful registration
- GIVEN valid username, email, and password
- WHEN the client submits registration
- THEN a new user account is created
- AND the response indicates success

#### Scenario: Duplicate identity
- GIVEN a username or email that already exists
- WHEN the client submits registration
- THEN the system rejects the request with a validation error

### Requirement: Username-based Login
The system SHALL authenticate users by username and password and issue a JWT session.

#### Scenario: Valid credentials
- GIVEN a registered user with correct password
- WHEN the client submits login
- THEN authentication succeeds
- AND user identity data is returned to the client

#### Scenario: Invalid credentials
- GIVEN invalid username or password
- WHEN the client submits login
- THEN authentication fails
- AND no session is established

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
- AND the client does not decode the JWT locally

#### Scenario: Missing session
- GIVEN no valid session
- WHEN the client requests the current user
- THEN the system responds with 401 Unauthorized

### Requirement: Logout
The system SHALL invalidate the client session on logout by clearing the HttpOnly cookie.

#### Scenario: Explicit logout
- GIVEN an authenticated session
- WHEN the client logs out
- THEN the session cookie is removed
- AND subsequent protected requests fail with 401

### Requirement: Security Headers
The system SHALL send HTTP security headers that mitigate XSS, clickjacking, and MIME sniffing.

#### Scenario: Document and API responses include headers
- GIVEN a request to the web or API origin
- WHEN the response is returned
- THEN security headers (including CSP-related protections) are present
