# Proposal: Test Infrastructure Refactoring

## Why
Убрать boilerplate в тестах: traits/helpers для DB, JWT auth, factories.

## What Changes
In scope:
- AuthenticationTrait
- DatabaseTestTrait
- TestAuthClient
- TestObjectFactory

Out of scope:
- New product features

## Impact
- Domains: api
- Legacy task: REFACTORING-TEST-INFRA
- Source archive: `memory-bank-archive/archive/archive-refactoring-test-infrastructure-20251130.md`

## Approach
Выделить переиспользуемые test helpers после CQST-005.
