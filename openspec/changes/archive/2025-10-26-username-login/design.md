# Design: Username-Based Authentication

## Technical Approach
Update Security user provider and JWT user identity to username.

## Architecture Decisions
### Decision: Username as public login identifier
Simpler memorable login; email remains account attribute.

## File Changes
- Security provider / getUserIdentifier
- Login DTO and tests

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-002-20251026.md` (CQST-002).
