# Implementation Plan: Laravel Event Manager

## Overview

This implementation plan breaks down the Laravel Event Manager into discrete coding tasks. The approach follows Laravel best practices with a service layer pattern, automatic event scoping through traits, and comprehensive testing. Each task builds incrementally, ensuring that core functionality is validated early through automated tests.

## Tasks

- [x] 0. Install Laravel and configure database connection
  - Install Laravel using Composer
  - Configure database credentials in .env file (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
  - Test database connectivity
  - Review existing database tables to understand the schema
  - Add EVENT_ID and ORG_ID to .env file
  - _Requirements: 11.1, 11.2, 11.3_

- [x] 1. Set up configuration and service provider
  - Create config/event.php to load EVENT_ID and ORG_ID from .env
  - Create EventServiceProvider to validate configuration on boot
  - Register EventServiceProvider in config/app.php
  - Add EVENT_ID and ORG_ID to .env.example
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 10.1, 10.2, 10.3, 10.4, 10.5_

- [ ]* 1.1 Write property test for configuration loading
  - **Property 1: Configuration Loading**
  - **Validates: Requirements 1.1, 1.2**

- [ ]* 1.2 Write unit tests for configuration validation
  - Test missing EVENT_ID throws exception
  - Test missing ORG_ID throws exception
  - _Requirements: 1.3, 1.4_

- [x] 2. Create database migrations (Partially Complete - Categories and Agenda Items done)
  - [ ] 2.1 Create hash_mappings table migration
    - Include id, hash (unique, 32 chars), model_type, model_id, event_id, org_id, timestamps
    - Add indexes on hash, model_type, model_id, event_id, org_id, and composite indexes
    - _Requirements: 12.1, 12.2_
  
  - [ ] 2.2 Create registrations table migration
    - Include id, event_id, org_id, name, email, phone, status, timestamps
    - Add indexes on event_id, org_id, and composite index on (event_id, org_id)
    - _Requirements: 2.1, 2.2_
  
  - [x] 2.3 Create categories table migration (tracks table exists)
    - Include id, event_id, org_id, name, description, color, timestamps
    - Add indexes on event_id, org_id, and composite index on (event_id, org_id)
    - _Requirements: 2.1, 2.2_
  
  - [x] 2.4 Create agenda_items table migration (event_sessions table exists)
    - Include id, event_id, org_id, category_id, title, description, start_time, end_time, location, timestamps
    - Add foreign key on category_id with onDelete('set null')
    - Add indexes on event_id, org_id, start_time, and composite index on (event_id, org_id)
    - _Requirements: 2.1, 2.2_

- [ ]* 2.5 Write unit tests for migration structure
  - Test all tables have required columns
  - Test all tables have proper indexes
  - Test foreign key constraints
  - _Requirements: 2.1, 2.2_

- [x] 3. Create HasEventScope trait and models (Partially Complete)
  - [x] 3.1 Create app/Traits/HasEventScope.php trait
    - Implement bootHasEventScope method with global scope for event_id and org_id filtering
    - Implement creating event to auto-assign event_id and org_id from config
    - _Requirements: 2.3, 2.4, 2.5, 2.6, 8.1, 8.2, 8.3, 8.4, 8.5_
  
  - [ ] 3.2 Create HashMapping model
    - Define fillable fields (hash, model_type, model_id, event_id, org_id)
    - Add morphTo relationship for hashable
    - _Requirements: 12.1, 12.2_
  
  - [ ] 3.3 Create app/Services/HashService.php
    - Implement generateHash method to create unique hash and store mapping
    - Implement resolveHash method to retrieve model from hash
    - Implement getHash method to retrieve hash for a model
    - Validate event_id and org_id when resolving hashes
    - _Requirements: 12.2, 12.3, 12.4, 12.5, 12.6_
  
  - [ ] 3.4 Create app/Traits/HasHashedRoutes.php trait
    - Implement bootHasHashedRoutes to auto-generate hash on model creation
    - Implement getHashAttribute accessor
    - Override getRouteKey and getRouteKeyName for route model binding
    - _Requirements: 12.2, 12.3_
  
  - [ ] 3.5 Create Registration model
    - Use HasEventScope and HasHashedRoutes traits
    - Define fillable fields
    - Add registered_at datetime cast
    - _Requirements: 2.3, 2.4, 2.5, 2.6, 12.2_
  
  - [x] 3.6 Create Category model
    - Use HasEventScope and HasHashedRoutes traits
    - Define fillable fields
    - Add hasMany relationship to AgendaItem
    - _Requirements: 2.3, 2.4, 2.5, 2.6, 9.5, 12.2_
  
  - [x] 3.7 Create AgendaItem model
    - Use HasEventScope and HasHashedRoutes traits
    - Define fillable fields
    - Add start_time and end_time datetime casts
    - Add belongsTo relationship to Category
    - _Requirements: 2.3, 2.4, 2.5, 2.6, 9.5, 12.2_

- [ ]* 3.8 Write property test for automatic field assignment
  - **Property 2: Automatic Field Assignment**
  - **Validates: Requirements 2.3, 2.4**

- [ ]* 3.9 Write property test for automatic query filtering
  - **Property 3: Automatic Query Filtering**
  - **Validates: Requirements 2.5, 2.6**

- [ ]* 3.10 Write property test for cross-event access prevention
  - **Property 4: Cross-Event Access Prevention**
  - **Validates: Requirements 4.4, 4.5, 5.4, 5.5, 6.4, 6.5**

- [ ]* 3.11 Write property test for model relationships preserve scope
  - **Property 5: Model Relationships Preserve Scope**
  - **Validates: Requirements 9.5**

- [ ]* 3.12 Write property test for hash generation on model creation
  - **Property 6: Hash Generation on Model Creation**
  - **Validates: Requirements 12.2**

- [ ]* 3.13 Write property test for hash resolution to correct model
  - **Property 7: Hash Resolution to Correct Model**
  - **Validates: Requirements 12.3, 12.4, 12.5**

- [ ]* 3.14 Write property test for invalid hash returns 404
  - **Property 8: Invalid Hash Returns 404**
  - **Validates: Requirements 12.6**

- [ ]* 3.15 Write unit tests for traits and models
  - Test HasEventScope trait exists and is applied to models
  - Test HasHashedRoutes trait exists and is applied to models
  - Test model relationships work correctly
  - Test HashService methods
  - _Requirements: 8.5, 9.5, 12.1, 12.2, 12.3, 12.4_

- [ ] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Create service classes (Partially Complete)
  - [ ] 5.1 Create app/Services/RegistrationService.php
    - Implement createRegistration, getAllRegistrations, updateRegistration, deleteRegistration methods
    - _Requirements: 4.3, 9.1, 9.2_
  
  - [x] 5.2 Create app/Services/CategoryService.php
    - Implement createCategory, getAllCategories, updateCategory, deleteCategory methods
    - _Requirements: 5.3, 9.1, 9.2_
  
  - [x] 5.3 Create app/Services/AgendaService.php
    - Implement createAgendaItem, getAllAgendaItems, updateAgendaItem, deleteAgendaItem methods
    - Include eager loading of category relationship
    - _Requirements: 6.3, 9.1, 9.2_

- [ ]* 5.4 Write unit tests for service classes
  - Test each service method with specific inputs
  - Test service classes exist
  - _Requirements: 9.2_

- [ ] 6. Create EventAdmin middleware
  - Create app/Http/Middleware/EventAdmin.php
  - Implement authentication check and redirect to login if not authenticated
  - Register middleware alias 'event.admin' in app/Http/Kernel.php
  - _Requirements: 7.1, 7.2, 7.3_

- [ ]* 6.1 Write unit tests for EventAdmin middleware
  - Test unauthenticated users are redirected
  - Test authenticated users can proceed
  - Test middleware is registered
  - _Requirements: 7.1, 7.2, 7.3_

- [ ] 7. Create public controllers and routes
  - [ ] 7.1 Create app/Http/Controllers/EventController.php
    - Implement landing method (displays categories and agenda)
    - Implement showRegistrationForm method
    - Implement storeRegistration method with validation
    - Inject CategoryService, AgendaService, RegistrationService
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 9.1_
  
  - [ ] 7.2 Add public routes to routes/web.php
    - GET /event -> EventController@landing
    - GET /event/register -> EventController@showRegistrationForm
    - POST /event/register -> EventController@storeRegistration
    - _Requirements: 3.1, 3.5_

- [ ]* 7.3 Write unit tests for public routes
  - Test /event route is accessible without authentication
  - Test /event/register routes exist
  - Test registration validation rules
  - _Requirements: 3.1, 3.2, 3.5_

- [ ] 8. Create admin controllers and routes
  - [ ] 8.1 Create app/Http/Controllers/Admin/DashboardController.php
    - Implement index method displaying event statistics
    - Inject all service classes
    - _Requirements: 7.4, 7.5, 9.1_
  
  - [ ] 8.2 Create app/Http/Controllers/Admin/RegistrationController.php
    - Implement resource controller methods (index, create, store, edit, update, destroy)
    - Use HashService to resolve hashes in edit, update, and destroy methods
    - Add validation rules for registration data
    - Inject RegistrationService and HashService
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 9.1, 12.3, 12.4_
  
  - [ ] 8.3 Create app/Http/Controllers/Admin/CategoryController.php
    - Implement resource controller methods (index, create, store, edit, update, destroy)
    - Use HashService to resolve hashes in edit, update, and destroy methods
    - Add validation rules for category data
    - Inject CategoryService and HashService
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 9.1, 12.3, 12.4_
  
  - [ ] 8.4 Create app/Http/Controllers/Admin/AgendaController.php
    - Implement resource controller methods (index, create, store, edit, update, destroy)
    - Use HashService to resolve hashes in edit, update, and destroy methods
    - Add validation rules for agenda item data (including end_time after start_time)
    - Inject AgendaService, CategoryService, and HashService
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 9.1, 12.3, 12.4_
  
  - [ ] 8.5 Add admin routes to routes/web.php
    - Group routes under /event/admin prefix with 'admin.' name prefix
    - Apply 'event.admin' middleware to all admin routes
    - Add dashboard route
    - Add resource routes for registrations, categories, agenda
    - _Requirements: 4.1, 5.1, 6.1, 7.2, 7.4_

- [ ]* 8.6 Write unit tests for admin routes and controllers
  - Test admin routes require authentication
  - Test admin dashboard displays statistics
  - Test controller methods return correct views
  - Test validation rules work correctly
  - _Requirements: 7.2, 7.3, 7.4, 7.5_

- [ ] 9. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Create Blade views for public pages
  - [ ] 10.1 Create resources/views/event/landing.blade.php
    - Display event information
    - Display categories using Blade components
    - Display agenda overview using Blade components
    - Include link to registration form
    - _Requirements: 3.2, 3.3, 3.4_
  
  - [ ] 10.2 Create resources/views/event/register.blade.php
    - Create registration form with name, email, phone fields
    - Include CSRF token and validation error display
    - _Requirements: 3.5_

- [ ] 11. Create Blade views for admin panel
  - [ ] 11.1 Create resources/views/admin/dashboard.blade.php
    - Display event management overview with statistics
    - Include navigation to registrations, categories, agenda sections
    - _Requirements: 7.5_
  
  - [ ] 11.2 Create resources/views/admin/registrations/ views
    - Create index.blade.php (list all registrations)
    - Create create.blade.php (registration form)
    - Create edit.blade.php (edit registration form)
    - _Requirements: 4.1, 4.2_
  
  - [ ] 11.3 Create resources/views/admin/categories/ views
    - Create index.blade.php (list all categories)
    - Create create.blade.php (category form)
    - Create edit.blade.php (edit category form)
    - _Requirements: 5.1, 5.2_
  
  - [ ] 11.4 Create resources/views/admin/agenda/ views
    - Create index.blade.php (list all agenda items)
    - Create create.blade.php (agenda item form with category dropdown)
    - Create edit.blade.php (edit agenda item form)
    - _Requirements: 6.1, 6.2_

- [ ] 12. Create reusable Blade components
  - Create resources/views/components/event-card.blade.php
  - Create resources/views/components/category-badge.blade.php
  - Create resources/views/components/agenda-item.blade.php
  - _Requirements: 9.3_

- [ ] 13. Create model factories for testing
  - Create database/factories/RegistrationFactory.php
  - Create database/factories/CategoryFactory.php
  - Create database/factories/AgendaItemFactory.php
  - Factories should use event_id and org_id from config by default
  - Allow overriding event_id and org_id for cross-event testing scenarios

- [ ]* 14. Write integration tests
  - Test end-to-end registration flow from public form
  - Test admin CRUD operations for registrations
  - Test admin CRUD operations for categories
  - Test admin CRUD operations for agenda items
  - Test authentication flow and middleware protection

- [ ] 15. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties with minimum 100 iterations
- Unit tests validate specific examples, edge cases, and integration points
- Service layer keeps controllers thin and business logic testable
- HasEventScope trait ensures automatic event/org filtering across all models
- HasHashedRoutes trait ensures hash-based URLs for edit and delete operations
- All admin routes are protected by EventAdmin middleware
- The application connects to an existing database and creates new tables only
- Hash-based URLs prevent ID enumeration attacks and enhance security
