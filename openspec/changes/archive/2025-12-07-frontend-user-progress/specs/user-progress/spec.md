# Delta for user-progress

> Historical delta migrated from Memory Bank archive `CQST-007-phase3`.
> Already reflected in `openspec/specs/user-progress/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Progress Listing
The system SHALL return the user's quest progress for profile history views.

#### Scenario: List progress
- GIVEN an authenticated user with progress records
- WHEN the client requests progress
- THEN matching progress items are returned
