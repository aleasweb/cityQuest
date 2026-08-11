# Delta for quest

> Historical delta migrated from Memory Bank archive `CQST-005`.
> Already reflected in `openspec/specs/quest/spec.md` — do not re-merge.

## ADDED Requirements

### Requirement: Quest Listing and Filters
The system SHALL list quests with optional filters for city, difficulty, and popularity, plus sorting and pagination.

#### Scenario: Filtered list
- GIVEN filter parameters
- WHEN the client requests the quest list
- THEN only matching quests are returned

### Requirement: Nearby Quest Search
The system SHALL find quests near given coordinates using geographic distance.

#### Scenario: Nearby search
- GIVEN user coordinates
- WHEN nearby quests are requested
- THEN results are ordered by distance
