# Delta for auth

> Historical delta migrated from Memory Bank archive `CQST-002`.
> Already reflected in `openspec/specs/auth/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Username-based Login
The system SHALL authenticate users by username and password and issue a JWT session.

#### Scenario: Valid credentials
- GIVEN a registered user with correct password
- WHEN the client submits login with username
- THEN authentication succeeds
