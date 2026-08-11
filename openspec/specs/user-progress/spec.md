# User Progress Specification

## Purpose
User quest lifecycle: start, pause, complete, abandon, checkpoint geovalidation, and progress history. Domain events record progress actions for audit and analytics.

## Requirements

### Requirement: Start Quest
The system SHALL allow an authenticated user to start a quest, creating active progress.

#### Scenario: Start when idle
- GIVEN an authenticated user with no active quest
- WHEN the user starts a quest
- THEN progress status becomes active
- AND current step is set to the first active step when steps exist

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

#### Scenario: Complete active quest
- GIVEN active progress
- WHEN the user completes
- THEN status becomes completed

### Requirement: Abandon Quest
The system SHALL allow abandoning progress for a quest.

#### Scenario: Abandon
- GIVEN existing progress for a quest
- WHEN the user abandons
- THEN the progress record is removed or otherwise cleared from active history as defined by the API

### Requirement: Checkpoint Geovalidation
The system SHALL validate the user's location against the current step radius and advance progress on success.

#### Scenario: Inside radius
- GIVEN active progress at step N
- WHEN the user checks in within the step radius
- THEN a successful check is recorded
- AND current step advances to the next active step if one exists

#### Scenario: Outside radius
- GIVEN active progress at step N
- WHEN the user checks in outside the step radius
- THEN the system rejects with 422
- AND the response includes distance information
- AND a failed check event is recorded

#### Scenario: Last active step
- GIVEN active progress on the last active step
- WHEN the user checks in successfully
- THEN the quest is completed automatically

#### Scenario: Quest not active
- GIVEN no active progress
- WHEN the user attempts a checkpoint check
- THEN the system responds with 403

### Requirement: Progress Listing
The system SHALL return the user's quest progress with optional filters (status, liked).

#### Scenario: List progress
- GIVEN an authenticated user with progress records
- WHEN the client requests progress
- THEN matching progress items are returned

### Requirement: Progress Domain Events
The system SHALL append domain events for progress lifecycle and checkpoint checks to an append-only event store.

#### Scenario: Event on start
- GIVEN a successful quest start
- WHEN progress is created
- THEN a start event is stored with progress, user, quest, and timestamp

#### Scenario: Event on checkpoint check
- GIVEN a checkpoint check attempt
- WHEN validation runs
- THEN a check event is stored including coordinates, distance, and pass/fail outcome
