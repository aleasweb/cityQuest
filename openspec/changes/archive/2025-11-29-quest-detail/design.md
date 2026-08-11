# Design: Quest Data API

## Technical Approach
Quest domain with Doctrine repository and public controller.

## Architecture Decisions
### Decision: Public quest detail without JWT
Discovery content is readable anonymously.

## File Changes
- Quest entity/repository/service/controller
- Migration for quests table

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-004-20251129.md` (CQST-004).
