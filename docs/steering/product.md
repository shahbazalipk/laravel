# Product Overview

Laravel Event Manager is a Blade-based web application for managing a single event within a SaaS ecosystem. The platform handles event registrations, categories, and agenda management while maintaining multi-tenant architecture through org_id and event_id separation.

## Core Features

- Public event landing page with registration
- Admin panel for managing registrations, categories, and agenda items
- Hash-based URL security to prevent ID enumeration
- Automatic event/organization scoping for data isolation
- Integration with existing SaaS database infrastructure

## Key Principles

- Single event focus with multi-tenant readiness
- Automatic data filtering through global scopes
- Service layer pattern for business logic
- Security-first approach with hashed identifiers
