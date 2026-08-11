# Design: Frontend User Progress Integration

## Technical Approach
Optimistic UI with rollback; modal for 409 active-quest conflict; Toast notifications.

## Architecture Decisions
### Decision: Like only for started quests (at that time)
Enforce in backend 403 and disable in UI.

## File Changes
- QuestDetail/UserProfile integration
- Toast, ActiveQuestModal, QuestCard

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-007-phase3-20251207.md` (CQST-007-phase3).
