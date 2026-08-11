# Design: User Profile Management

## Technical Approach
ProfileService with three operations; public endpoint without email.

## Architecture Decisions
### Decision: Split public vs private profile fields
Email only on owner profile endpoints.

## File Changes
- UpdateProfileRequest, ProfileService, ProfileController
- security.yaml public firewall for GET /api/users/{username}

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-003-20251026.md` (CQST-003).
