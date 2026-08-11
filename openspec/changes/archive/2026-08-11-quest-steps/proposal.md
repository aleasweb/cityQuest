# Proposal: Quest Steps Implementation

## Why
Чекпоинты квеста: QuestStep, geo check, current_step_number, auto-complete на последнем шаге.

## What Changes
In scope:
- quest_steps table / QuestStep entity
- Quest.type linear
- GET steps + POST check
- GeolocationService in Shared/Geo
- QuestStepCheckEvent

Out of scope:
- RANDOM completion type
- Frontend/mobile step UI

## Impact
- Domains: quest, user-progress
- Legacy task: CQST-012
- Source archive: `memory-bank-archive/archive/archive-CQST-012.md`

## Approach
QuestStep in Quest domain; Shared Geo Haversine; progress advances via check endpoint.
