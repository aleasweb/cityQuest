# Design: Registration and Authentication System

## Technical Approach
User domain (Domain/Application/Infrastructure/Presentation) + Lexik JWT.

## Architecture Decisions
### Decision: JWT for API auth
Stateless REST auth for Web/mobile clients.

### Decision: DDD layering for User
Foundation pattern for subsequent domains.

## File Changes
- User Entity/Repository/AuthenticationService/AuthController
- security.yaml + Lexik JWT config
- PHPUnit coverage for register/login

## Source
Migrated from `memory-bank-archive/archive/archive-CQST-001-20251025.md` (CQST-001).
