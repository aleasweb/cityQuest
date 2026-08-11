# Design: Quest Steps Implementation

## Technical Approach
QuestStep INTEGER ids; no FK/CASCADE to quests; GeolocationService extracted to Shared/Geo; auto-complete on last active step.

## Architecture Decisions
### Decision: No FK on quest_steps.quest_id
Keep historical steps if quest deleted later.

### Decision: Auto-complete on last step
Simplify client: successful last check completes quest.

## File Changes
- QuestStep entity/repo/service/controller
- UserProgressService start/check modifications
- Shared/Geo GeolocationService

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-012.md` (CQST-012).
