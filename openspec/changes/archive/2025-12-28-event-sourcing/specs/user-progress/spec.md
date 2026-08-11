# Delta for user-progress

> Historical delta migrated from Memory Bank archive `CQST-010`.
> Already reflected in `openspec/specs/user-progress/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Progress Domain Events
The system SHALL append domain events for progress lifecycle actions to an append-only event store.

#### Scenario: Event on start
- GIVEN a successful quest start
- WHEN progress is created
- THEN a start event is stored with progress, user, quest, and timestamp
