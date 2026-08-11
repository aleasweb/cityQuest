# Delta for auth

> Historical delta migrated from Memory Bank archive `CQST-001`.
> Already reflected in `openspec/specs/auth/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: User Registration
The system SHALL allow a new user to register with username, email, and password.

#### Scenario: Successful registration
- GIVEN valid registration data
- WHEN the client submits registration
- THEN a user account is created

### Requirement: Login Issues JWT
The system SHALL authenticate with credentials and issue a JWT session.

#### Scenario: Valid login
- GIVEN a registered user
- WHEN the client submits valid credentials
- THEN authentication succeeds and a session token is issued
