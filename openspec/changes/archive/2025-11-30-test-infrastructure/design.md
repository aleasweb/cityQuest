# Design: Test Infrastructure Refactoring

## Technical Approach
Shared test traits/helpers in PHPUnit suite; AuthenticationTrait for controllers.

## Architecture Decisions
### Decision: Real JWT via TestAuthClient
Prefer login-flow tokens over forging JWT in tests.

## File Changes
- Test helpers under tests/
- AuthenticationTrait for controllers

## Source
Migrated from `memory-bank-archive/archive/archive-refactoring-test-infrastructure-20251130.md` (REFACTORING-TEST-INFRA).
