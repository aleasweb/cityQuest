# Delta for user-progress

> Historical delta migrated from Memory Bank archive `CQST-012`.
> Already reflected in `openspec/specs/user-progress/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Checkpoint Geovalidation
The system SHALL validate the user's location against the current step radius and advance progress on success.

#### Scenario: Inside radius
- GIVEN active progress at step N
- WHEN the user checks in within the step radius
- THEN progress advances to the next active step if one exists

#### Scenario: Outside radius
- GIVEN active progress at step N
- WHEN the user checks in outside the step radius
- THEN the system rejects with 422

#### Scenario: Last active step
- GIVEN active progress on the last active step
- WHEN the user checks in successfully
- THEN the quest is completed automatically
