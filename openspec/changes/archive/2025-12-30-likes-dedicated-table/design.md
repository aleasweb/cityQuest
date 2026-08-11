# Design: Likes System Dedicated Table

## Technical Approach
Dedicated quest_likes with UNIQUE(user,quest); QuestLikeService; denormalized likesCount updates.

## Architecture Decisions
### Decision: Like requires progress in any status
UX: can like paused/completed, not only active.

## File Changes
- Migration quest_likes
- QuestLike entity/repo/service
- UserProgressService N+1 fix

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-011-20251230.md` (CQST-011).
