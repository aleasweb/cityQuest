# Proposal: Registration and Authentication System

## Why
Заложить JWT-аутентификацию MVP: регистрация и логин по REST API с DDD-слоями.

## What Changes
In scope:
- POST /api/auth/register
- POST /api/auth/login
- JWT (Lexik) + Symfony Security
- User domain (Entity/Repository/Service/Controller)
- Unit/integration tests

Out of scope:
- Profile endpoints
- Refresh tokens
- Frontend auth UI

## Impact
- Domains: auth
- Legacy task: CQST-001
- Source archive: `memory-bank-archive/archive/archive-CQST-001-20251025.md`

## Approach
DDD User bounded context + LexikJWTAuthenticationBundle; password hashing через Security.
