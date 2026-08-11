# Proposal: Username-Based Authentication

## Why
Перейти с login по email на login по username; JWT идентифицирует пользователя по username.

## What Changes
In scope:
- POST /api/auth/login принимает username
- JWT subject/username claim
- Security provider getUserIdentifier → username
- Обновление тестов и systemPatterns

Out of scope:
- Registration field changes beyond identifier
- Frontend AuthModal

## Impact
- Domains: auth
- Legacy task: CQST-002
- Source archive: `memory-bank-archive/archive/archive-CQST-002-20251026.md`

## Approach
Сменить user identifier с email на username во Security и JWT pipeline.
