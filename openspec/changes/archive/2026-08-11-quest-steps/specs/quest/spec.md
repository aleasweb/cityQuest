# Delta for quest

> Historical delta migrated from Memory Bank archive `CQST-012`.
> Already reflected in `openspec/specs/quest/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Linear Quest Steps
The system SHALL model quests as a sequence of active steps with coordinates and a validation radius.

#### Scenario: First active step on start
- GIVEN a quest with one or more active steps
- WHEN the user starts the quest
- THEN progress tracks the lowest active step number

### Requirement: Step Content for Active Quest
The system SHALL return step content only when the requesting user has an active quest progress for that quest.

#### Scenario: Inactive quest
- GIVEN a user without active progress on the quest
- WHEN the client requests a step
- THEN the system responds with 403
