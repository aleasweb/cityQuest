# Delta for user-progress

> Historical delta migrated from Memory Bank archive `CQST-005`.
> Already reflected in `openspec/specs/user-progress/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Start Quest
The system SHALL allow an authenticated user to start a quest, creating active progress.

#### Scenario: Conflict with existing active quest
- GIVEN an authenticated user who already has an active quest
- WHEN the user attempts to start another quest
- THEN the system responds with 409 Conflict

### Requirement: Pause and Complete
The system SHALL allow pausing or completing an active quest.

#### Scenario: Pause active quest
- GIVEN active progress
- WHEN the user pauses
- THEN status becomes paused
