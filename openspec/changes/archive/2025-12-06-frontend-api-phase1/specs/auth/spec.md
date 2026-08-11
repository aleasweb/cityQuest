# Delta for auth

> Historical delta migrated from Memory Bank archive `CQST-007-phase1`.
> Already reflected in `openspec/specs/auth/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Client Auth Modal
The web client SHALL provide register/login UI wired to auth API.

#### Scenario: Login via modal
- GIVEN a visitor on the site
- WHEN the user submits login in AuthModal
- THEN the client establishes an authenticated session for subsequent API calls
