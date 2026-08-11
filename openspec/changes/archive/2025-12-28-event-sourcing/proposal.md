# Proposal: UserProgress Domain Events & Event Sourcing

## Why
Ввести Event Sourcing для UserProgress: append-only domain_events_progress + domain events.

## What Changes
In scope:
- Abstract events + RecordsEvents
- Event store (DBAL)
- Lifecycle events on progress actions
- PlatformResolver bonus

Out of scope:
- Event handlers/projections UI
- Other domains' event stores

## Impact
- Domains: user-progress
- Legacy task: CQST-010
- Source archive: `memory-bank-archive/archive/archive-CQST-010-20251228.md`

## Approach
Append-only event table; record events from UserProgress aggregate transitions.
