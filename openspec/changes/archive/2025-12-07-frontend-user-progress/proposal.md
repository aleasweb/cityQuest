# Proposal: Frontend User Progress Integration

## Why
UI для like/start/pause/abandon и истории квестов; правило like только для начатых.

## What Changes
In scope:
- Optimistic like UI
- Start quest + 409 modal
- Pause/abandon
- Quest history in profile
- Toast / ActiveQuestModal / QuestCard

Out of scope:
- Checkpoint map UI
- HttpOnly cookie migration

## Impact
- Domains: user-progress, quest
- Legacy task: CQST-007-phase3
- Source archive: `memory-bank-archive/archive/archive-CQST-007-phase3-20251207.md`

## Approach
Wire existing progress/like APIs; duplicate business rule on FE+BE.
