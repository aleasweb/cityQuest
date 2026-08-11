# Proposal: Frontend API Integration Phase 1

## Why
Базовая интеграция React↔API: CORS, cities, безопасный JWT decode, AuthModal.

## What Changes
In scope:
- nelmio/cors-bundle
- GET /api/cities
- jwt-decode instead of atob
- AuthModal in Header

Out of scope:
- Quest progress UI
- HttpOnly cookies (later CQST-008)

## Impact
- Domains: api, auth
- Legacy task: CQST-007-phase1
- Source archive: `memory-bank-archive/archive/archive-CQST-007-phase1-20251206.md`

## Approach
Wire frontend auth modal to existing auth API; fix CORS and JWT client parsing.
