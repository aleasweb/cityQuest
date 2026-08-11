# Design: UserProgress Domain Events & Event Sourcing

## Technical Approach
Domain events hierarchy + EventStore via DBAL; sync messenger where applicable.

## Architecture Decisions
### Decision: Append-only event log
Prefer audit/analytics history over mutable state table for events.

## File Changes
- UserProgress domain events + EventStore
- domain_events_progress migration
- PlatformResolver / Platform VO

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-010-20251228.md` (CQST-010).
