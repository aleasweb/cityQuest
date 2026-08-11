# Quest Specification

## Purpose
Quest discovery, details, geosearch, likes, and checkpoint (step) content for active quests.

## Requirements

### Requirement: Quest Detail
The system SHALL return quest details by ID for public clients.

#### Scenario: Existing quest
- GIVEN a known quest ID
- WHEN the client requests quest details
- THEN the quest payload is returned in the standard API envelope

#### Scenario: Missing quest
- GIVEN an unknown quest ID
- WHEN the client requests quest details
- THEN the system responds with 404

### Requirement: Quest Listing and Filters
The system SHALL list quests with optional filters for city, difficulty, and popularity, plus sorting and pagination.

#### Scenario: Filtered list
- GIVEN filter parameters
- WHEN the client requests the quest list
- THEN only matching quests are returned
- AND pagination metadata is included when applicable

### Requirement: Nearby Quest Search
The system SHALL find quests near given coordinates using geographic distance.

#### Scenario: Nearby search
- GIVEN user latitude and longitude
- WHEN the client requests nearby quests
- THEN quests are ordered by distance from the given point

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

### Requirement: Linear Quest Steps
The system SHALL model quests as a sequence of active steps with coordinates and a validation radius. Quest type defaults to linear sequential completion.

#### Scenario: First active step on start
- GIVEN a quest with one or more active steps
- WHEN the user starts the quest
- THEN progress tracks the lowest active step number

### Requirement: Step Content for Active Quest
The system SHALL return step content only when the requesting user has an active quest progress for that quest.

#### Scenario: Active user requests step
- GIVEN an authenticated user with active progress
- WHEN the client requests a step by number
- THEN step content (text, media, coordinates, radius) is returned

#### Scenario: Inactive quest
- GIVEN a user without active progress on the quest
- WHEN the client requests a step
- THEN the system responds with 403

#### Scenario: Missing or inactive step
- GIVEN an active quest
- WHEN the client requests a non-existent or inactive step number
- THEN the system responds with 404
