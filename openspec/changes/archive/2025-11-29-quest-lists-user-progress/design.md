# Design: Quest Lists & User Progress API

## Technical Approach
Extend Quest listing/geosearch; add UserProgress aggregate with status machine and likes.

## Architecture Decisions
### Decision: Haversine in application SQL
Avoid PostGIS for MVP geosearch.

### Decision: Single active quest
Return 409 when starting while another quest is active.

## File Changes
- Quest list/nearby endpoints
- UserQuestProgress entity/services/controllers

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-005-20251129.md` (CQST-005).
