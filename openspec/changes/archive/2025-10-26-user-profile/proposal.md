# Proposal: User Profile Management

## Why
Добавить управление профилем: свой профиль (с email), публичный профиль, PATCH своего профиля.

## What Changes
In scope:
- GET /api/user/profile
- GET /api/users/{username}
- PATCH /api/user/profile
- ProfileService + tests + Postman

Out of scope:
- Avatar upload
- Quest history in profile

## Impact
- Domains: auth
- Legacy task: CQST-003
- Source archive: `memory-bank-archive/archive/archive-CQST-003-20251026.md`

## Approach
ProfileService в User application layer; разделение public/private полей.
