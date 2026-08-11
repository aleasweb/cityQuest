# Proposal: Frontend Token Security Enhancement

## Why
Закрыть XSS-риск JWT в localStorage: Security Headers + HttpOnly cookies. Phases 3–4 (refresh/CSRF) отменены.

## What Changes
In scope:
- Nginx security headers + CSP
- JWT in HttpOnly cookie
- GET /api/auth/me
- credentials: include on frontend

Out of scope:
- Refresh token mechanism
- CSRF protection

## Impact
- Domains: auth
- Legacy task: CQST-008
- Source archive: `memory-bank-archive/archive/archive-CQST-008-20251224.md`

## Approach
Lexik cookie extractors + CORS credentials; remove client JWT storage/decode.
