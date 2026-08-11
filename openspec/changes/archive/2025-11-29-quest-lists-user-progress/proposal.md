# Proposal: Quest Lists & User Progress API

## Why
Списки квестов (фильтры, nearby) и UserProgress (start/pause/complete, likes, один active).

## What Changes
In scope:
- GET /api/quests, GET /api/quests/nearby
- Like toggle
- UserProgress domain + start/pause/complete
- Business rule: one active quest → 409

Out of scope:
- Quest steps/checkpoints
- Frontend integration

## Impact
- Domains: quest, user-progress
- Legacy task: CQST-005
- Source archive: `memory-bank-archive/archive/archive-CQST-005-20251129.md`

## Approach
QuestListService + UserProgress domain; Haversine без PostGIS; Enum статусов.
