# Proposal: Client-side Caching for Cities

## Why
Кешировать /api/cities на клиенте (TTL 1h) со stale fallback при ошибках сети.

## What Changes
In scope:
- CacheManager utility
- api.getCities cache integration
- Dev helpers clear/isValid

Out of scope:
- Server-side Redis cache
- Caching other endpoints

## Impact
- Domains: api
- Legacy task: CQST-009
- Source archive: `memory-bank-archive/archive/archive-CQST-009-20251225.md`

## Approach
localStorage CacheManager with TTL + getStale fallback.
