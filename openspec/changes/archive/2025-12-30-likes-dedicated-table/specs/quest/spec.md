# Delta for quest

> Historical delta migrated from Memory Bank archive `CQST-011`.
> Already reflected in `openspec/specs/quest/spec.md` — do not re-merge.

## MODIFIED Requirements

### Requirement: Quest Likes
The system SHALL allow an authenticated user to like or unlike a quest that appears in their progress history (active, paused, or completed).

#### Scenario: Toggle like for quest in progress
- GIVEN an authenticated user with progress on a quest
- WHEN the user toggles like
- THEN the like state and quest like count are updated

#### Scenario: Like without progress
- GIVEN an authenticated user with no progress on a quest
- WHEN the user attempts to like
- THEN the system rejects the request with 403
