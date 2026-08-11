# Proposal: Likes System Dedicated Table

## Why
Вынести лайки в quest_likes; лайк разрешён для любого progress status; убрать N+1.

## What Changes
In scope:
- quest_likes table + FK
- QuestLike entity/service in Quest domain
- Like allowed for active/paused/completed
- meta.liked counter / batch liked map

Out of scope:
- Analytics period queries
- Remove deprecated is_liked column fully (follow-up)

## Impact
- Domains: quest
- Legacy task: CQST-011
- Source archive: `memory-bank-archive/archive/archive-CQST-011-20251230.md`

## Approach
Move likes ownership to Quest domain; batch queries for liked status.
