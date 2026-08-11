# Delta for auth

> Historical delta migrated from Memory Bank archive `CQST-003`.
> Already reflected in `openspec/specs/auth/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Owner Profile
The system SHALL let an authenticated user read and update their own profile including email.

#### Scenario: Get own profile
- GIVEN a valid session
- WHEN the client requests the owner profile
- THEN profile data including email is returned

### Requirement: Public Profile
The system SHALL expose another user's public profile without email.

#### Scenario: Get public profile
- GIVEN an existing username
- WHEN the client requests GET /api/users/{username}
- THEN public profile fields are returned without email
