# Proposal: Quest Data API

## Why
Ввести Quest domain и публичный GET /api/quests/{id}.

## What Changes
In scope:
- Quest entity (12 fields, UUID)
- QuestService/Repository/Controller
- Migration + tests

Out of scope:
- Quest lists
- Likes
- Progress

## Impact
- Domains: quest
- Legacy task: CQST-004
- Source archive: `memory-bank-archive/archive/archive-CQST-004-20251129.md`

## Approach
Новый Quest bounded context по DDD-шаблону User.
