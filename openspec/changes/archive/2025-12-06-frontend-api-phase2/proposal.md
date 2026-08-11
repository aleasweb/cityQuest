# Proposal: Frontend API Integration Phase 2

## Why
Довести consistency envelope API и фильтр isPopular; проверить HomePage/QuestDetail на реальных данных.

## What Changes
In scope:
- GET /api/quests/{id} → {data: quest}
- isPopular filter end-to-end
- Manual/browser verification

Out of scope:
- Like/start/pause UI (phase3)

## Impact
- Domains: api, quest
- Legacy task: CQST-007-phase2
- Source archive: `memory-bank-archive/archive/archive-CQST-007-phase2-20251206.md`

## Approach
Align remaining endpoints to envelope; wire isPopular in types/API/hooks.
