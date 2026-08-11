# Delta for quest

> Historical delta migrated from Memory Bank archive `CQST-004`.
> Already reflected in `openspec/specs/quest/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Quest Detail
The system SHALL return quest details by ID for public clients.

#### Scenario: Existing quest
- GIVEN a known quest ID
- WHEN the client requests quest details
- THEN the quest payload is returned

#### Scenario: Missing quest
- GIVEN an unknown quest ID
- WHEN the client requests quest details
- THEN the system responds with 404
