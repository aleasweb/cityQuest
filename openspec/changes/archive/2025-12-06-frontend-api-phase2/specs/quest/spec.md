# Delta for quest

> Historical delta migrated from Memory Bank archive `CQST-007-phase2`.
> Already reflected in `openspec/specs/quest/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Popular Filter
The system SHALL support filtering quests by popularity flag.

#### Scenario: isPopular filter
- GIVEN isPopular query parameter
- WHEN the client requests the quest list
- THEN only matching popular quests are returned
