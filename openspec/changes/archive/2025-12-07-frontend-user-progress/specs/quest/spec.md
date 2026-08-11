# Delta for quest

> Historical delta migrated from Memory Bank archive `CQST-007-phase3`.
> Already reflected in `openspec/specs/quest/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Quest Likes (started only)
The system SHALL allow liking only quests the user has started (historical rule for this change; later relaxed in CQST-011 to any progress status).

#### Scenario: Like without start
- GIVEN an authenticated user with no progress on a quest
- WHEN the user attempts to like
- THEN the system rejects the request with 403
