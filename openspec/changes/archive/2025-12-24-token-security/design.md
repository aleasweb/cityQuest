# Design: Frontend Token Security Enhancement

## Technical Approach
Phase 1 headers in Nginx; Phase 2 HttpOnly JWT cookie + /auth/me; logout clears cookie.

## Architecture Decisions
### Decision: Cancel refresh/CSRF for now
Ship critical XSS mitigations first; defer phases 3–4.

## File Changes
- nginx security headers
- lexik JWT cookie config
- AuthContext/api credentials include

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-008-20251224.md` (CQST-008).
