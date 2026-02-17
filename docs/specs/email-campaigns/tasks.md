# Implementation Plan: Email Campaigns and Templates System

## Overview

This implementation plan breaks down the email campaigns feature into discrete, manageable tasks. The approach follows Laravel Event Manager patterns with service layer architecture, queue-based processing, and multi-provider support. Tasks are organized to build incrementally, with early validation through testing.

## Tasks

- [x] 1. Database migrations and models setup
  - Create all 6 database migrations (email_templates, email_campaigns, email_campaign_recipients, email_campaign_logs, email_provider_configs, email_unsubscribes)
  - Create corresponding Eloquent models with relationships, casts, and scopes
  - Apply HasEventScope and HasHashedRoutes traits
  - Add soft deletes where appropriate
  - _Requirements: AC-1.1, AC-2.1, AC-3.1_

- [x] 2. Implement MergeCodeService for template variable processing
  - [x] 2.1 Create MergeCodeService with parse, replace, validate methods
    - Implement regex-based merge code parsing ({{code_name}} pattern)
    - Implement replacement logic with default values
    - Define standard merge codes (first_name, last_name, email, etc.)
    - Support custom field codes
    - _Requirements: AC-1.2_
  
  - [ ]* 2.2 Write property test for merge code validation
    - **Property 1: Merge Code Validation Correctness**
    - **Validates: Requirements AC-1.2**
  
  - [ ]* 2.3 Write property test for merge code replacement
    - **Property 2: Merge Code Replacement Completeness**
    - **Validates: Requirements AC-1.2**

- [x] 3. Implement TemplateService for email template management
  - [x] 3.1 Create TemplateService with CRUD operations
    - Implement create, update, delete methods
    - Implement clone method for template duplication
    - Implement preview method with sample data
    - Integrate MergeCodeService for validation
    - Implement renderTemplate method (returns HTML and text)
    - _Requirements: AC-1.1, AC-1.3_
  
  - [ ]* 3.2 Write property test for template cloning
    - **Property 3: Template Cloning Equivalence**
    - **Validates: Requirements AC-1.3**
  
  - [ ]* 3.3 Write unit tests for template CRUD operations
    - Test create with valid/invalid data
    - Test soft delete behavior
    - Test usage count tracking
    - _Requirements: AC-1.1, AC-1.3_

- [x] 4. Implement email provider abstraction layer
  - [x] 4.1 Create EmailProviderInterface
    - Define interface methods (send, sendBatch, getStatus, getStatistics, testConnection)
    - Define return value contracts
    - _Requirements: AC-3.2_
  
  - [x] 4.2 Create ProviderManager service
    - Implement provider registration and selection
    - Implement default provider logic
    - Implement failover mechanism (try primary, fall back to SMTP)
    - Load provider configs from database
    - _Requirements: AC-3.2_
  
  - [x] 4.3 Implement SmtpProvider (Laravel Mail)
    - Implement EmailProviderInterface
    - Use Laravel Mail facade for sending
    - Basic tracking (sent/failed only)
    - _Requirements: AC-3.2_
  
  - [x] 4.4 Implement InfobipProvider
    - Implement EmailProviderInterface
    - Integrate Infobip Email API
    - Support tracking (opens, clicks, bounces)
    - Implement webhook signature validation
    - _Requirements: AC-3.2_
  
  - [x] 4.5 Implement MailchimpProvider
    - Implement EmailProviderInterface
    - Integrate Mailchimp Transactional API
    - Support tracking and analytics
    - Implement webhook signature validation
    - _Requirements: AC-3.2_
  
  - [ ]* 4.6 Write property test for provider credential encryption
    - **Property 7: Provider Credential Encryption Round-Trip**
    - **Validates: Requirements AC-3.1**
  
  - [ ]* 4.7 Write unit tests for provider failover
    - Test primary provider failure triggers fallback
    - Test SMTP fallback works
    - _Requirements: AC-3.2_

- [ ] 5. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement RecipientService for recipient management
  - [x] 6.1 Create RecipientService with recipient resolution
    - Implement resolveFromRegistrations with filters
    - Implement resolveFromCsv with column mapping
    - Implement resolveFromSegment
    - Implement email validation (RFC 5322)
    - Implement deduplication logic
    - Implement excludeUnsubscribed filtering
    - _Requirements: AC-2.2_
  
  - [ ]* 6.2 Write property test for email deduplication
    - **Property 4: Recipient Email Deduplication**
    - **Validates: Requirements AC-2.2**
  
  - [ ]* 6.3 Write property test for CSV email validation
    - **Property 5: CSV Email Validation**
    - **Validates: Requirements AC-2.2**
  
  - [ ]* 6.4 Write unit tests for recipient filtering
    - Test registration category filter
    - Test payment status filter
    - Test date range filter
    - _Requirements: AC-2.2_

- [x] 7. Implement CampaignService for campaign lifecycle
  - [x] 7.1 Create CampaignService with campaign management
    - Implement create, update, delete methods
    - Implement schedule method (immediate or future)
    - Implement send method (dispatches queue jobs)
    - Implement pause, resume, cancel methods
    - Implement getStatistics method
    - Implement exportReport method (CSV export)
    - _Requirements: AC-2.1, AC-2.3_
  
  - [ ]* 7.2 Write unit tests for campaign state transitions
    - Test draft → scheduled → sending → completed
    - Test pause and resume
    - Test cancel from various states
    - _Requirements: AC-2.1, AC-2.3_

- [x] 8. Implement queue jobs for asynchronous sending
  - [x] 8.1 Create SendCampaignJob
    - Load campaign and recipients
    - Chunk recipients into batches (100 per batch)
    - Dispatch SendEmailBatchJob for each batch
    - Update campaign status (sending → completed)
    - Handle failures and mark campaign as failed
    - _Requirements: AC-2.3, AC-3.3_
  
  - [x] 8.2 Create SendEmailBatchJob
    - Configure retry logic (3 attempts, exponential backoff)
    - Get provider from ProviderManager
    - Render template for each recipient using MergeCodeService
    - Send batch via provider
    - Log results via TrackingService
    - Apply rate limiting middleware
    - _Requirements: AC-2.3, AC-3.3_
  
  - [ ]* 8.3 Write property test for batch processing completeness
    - **Property 8: Queue Batch Processing Completeness**
    - **Validates: Requirements AC-3.3**
  
  - [ ]* 8.4 Write property test for retry limit enforcement
    - **Property 9: Job Retry Limit Enforcement**
    - **Validates: Requirements AC-3.3**
  
  - [ ]* 8.5 Write unit tests for job error handling
    - Test provider timeout handling
    - Test network error retry
    - Test invalid email handling
    - _Requirements: AC-2.3_

- [x] 9. Implement TrackingService for analytics
  - [x] 9.1 Create TrackingService with event logging
    - Implement logSent, logDelivered, logOpened, logClicked, logBounced, logFailed
    - Implement getRecipientStatus method
    - Implement getCampaignStatistics method
    - Implement aggregateStatistics method (updates campaign counters)
    - _Requirements: AC-2.4_
  
  - [ ]* 9.2 Write property test for statistics consistency
    - **Property 6: Campaign Statistics Consistency**
    - **Validates: Requirements AC-2.4**
  
  - [ ]* 9.3 Write unit tests for tracking events
    - Test each event type logs correctly
    - Test statistics aggregation accuracy
    - _Requirements: AC-2.4_

- [ ] 10. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Implement UnsubscribeService for compliance
  - [x] 11.1 Create UnsubscribeService
    - Implement unsubscribe method (creates record)
    - Implement resubscribe method
    - Implement isUnsubscribed check
    - Implement getUnsubscribeUrl with encrypted hash
    - Implement handleUnsubscribeRequest
    - _Requirements: US-10_
  
  - [ ]* 11.2 Write property test for unsubscribe immediate effect
    - **Property 10: Unsubscribe Immediate Effect**
    - **Validates: Requirements US-10**
  
  - [ ]* 11.3 Write unit tests for unsubscribe flow
    - Test unsubscribe URL generation
    - Test hash validation
    - Test unsubscribe persistence
    - _Requirements: US-10_

- [x] 12. Create admin controllers for email templates
  - [x] 12.1 Create EmailTemplateController
    - Implement index (list templates with filters)
    - Implement create (show form)
    - Implement store (validate and create)
    - Implement edit (show form with data)
    - Implement update (validate and update)
    - Implement destroy (soft delete)
    - Implement clone action
    - Implement preview action (with sample data)
    - Delegate all logic to TemplateService
    - _Requirements: AC-1.1, AC-1.3_
  
  - [ ]* 12.2 Write integration tests for template routes
    - Test CRUD operations via HTTP
    - Test validation errors
    - Test authentication required
    - _Requirements: AC-1.1, AC-1.3_

- [x] 13. Create admin controllers for email campaigns
  - [x] 13.1 Create EmailCampaignController
    - Implement index (list campaigns with status filters)
    - Implement create (show form with template selection)
    - Implement store (validate and create)
    - Implement show (campaign details and statistics)
    - Implement edit (show form)
    - Implement update (validate and update)
    - Implement destroy (soft delete)
    - Implement send action (queue campaign)
    - Implement pause, resume, cancel actions
    - Delegate all logic to CampaignService
    - _Requirements: AC-2.1, AC-2.3_
  
  - [x] 13.2 Create RecipientUploadController
    - Implement upload form
    - Implement store (parse CSV, validate, preview)
    - Implement confirm (save recipients to campaign)
    - Use RecipientService for processing
    - _Requirements: AC-2.2_
  
  - [ ]* 13.3 Write integration tests for campaign routes
    - Test campaign creation flow
    - Test CSV upload flow
    - Test send/pause/resume/cancel actions
    - Test authentication required
    - _Requirements: AC-2.1, AC-2.2, AC-2.3_

- [ ] 14. Create webhook controller for provider events
  - [x] 14.1 Create EmailWebhookController
    - Implement Infobip webhook handler
    - Implement Mailchimp webhook handler
    - Validate webhook signatures
    - Parse webhook payload
    - Call TrackingService to log events
    - Return appropriate responses
    - _Requirements: AC-2.4_
  
  - [ ]* 14.2 Write unit tests for webhook processing
    - Test signature validation
    - Test event parsing
    - Test tracking service integration
    - _Requirements: AC-2.4_

- [x] 15. Create admin views for email templates
  - [x] 15.1 Create template index view
    - Display templates table with name, category, usage count, status
    - Add filters (category, active/inactive)
    - Add search by name
    - Add "Create Template" button
    - Add action buttons (edit, clone, delete)
    - Show empty state when no templates
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-1.1, AC-1.3_
  
  - [x] 15.2 Create template create/edit form view
    - Form fields: name, category, subject, html_content, text_content, description
    - Integrate TinyMCE rich text editor
    - Add merge code picker dropdown
    - Add preview button (opens modal)
    - Add save and cancel buttons
    - Display validation errors
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-1.1_
  
  - [x] 15.3 Create template preview modal
    - Display rendered HTML in iframe
    - Show plain text version
    - Allow sample data input
    - Mobile and desktop preview toggle
    - _Requirements: AC-1.1_

- [x] 16. Create admin views for email campaigns
  - [x] 16.1 Create campaign index view
    - Display campaigns table with name, status, recipients, statistics
    - Add filters (status, date range)
    - Add search by name
    - Add "Create Campaign" button
    - Add action buttons (view, edit, send, pause, cancel, delete)
    - Show status badges with colors
    - Show progress bar for sending campaigns
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-2.1, AC-2.3_
  
  - [x] 16.2 Create campaign create/edit form view
    - Step 1: Select template
    - Step 2: Configure campaign (name, sender, reply-to)
    - Step 3: Select recipients (registrations, CSV, segment)
    - Step 4: Schedule (immediate or future)
    - Step 5: Review and confirm (show recipient count)
    - Multi-step wizard with progress indicator
    - Display validation errors
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-2.1, AC-2.2_
  
  - [x] 16.3 Create campaign detail view
    - Display campaign information
    - Display real-time statistics with charts
    - Display recipient list with status
    - Display send progress
    - Add export report button
    - Add pause/resume/cancel buttons
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-2.3, AC-2.4_
  
  - [x] 16.4 Create CSV upload view
    - File upload dropzone
    - Column mapping interface
    - Preview first 10 rows
    - Show validation errors
    - Show duplicate count
    - Confirm button
    - _Requirements: AC-2.2_

- [x] 17. Create admin views for provider configuration
  - [x] 17.1 Create provider config index view
    - Display configured providers with status
    - Add "Add Provider" button
    - Add action buttons (edit, test, delete)
    - Show default provider indicator
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-3.1_
  
  - [x] 17.2 Create provider config form view
    - Provider selection dropdown (Infobip, Mailchimp, SMTP)
    - Dynamic credential fields based on provider
    - Settings fields (rate limit, etc.)
    - Test connection button
    - Set as default checkbox
    - Display validation errors
    - Follow UI/UX standards from ui-ux-standards.md
    - _Requirements: AC-3.1_

- [ ] 18. Create public unsubscribe page
  - [x] 18.1 Create UnsubscribeController
    - Implement show method (display unsubscribe form)
    - Implement store method (process unsubscribe)
    - Validate hash parameter
    - Call UnsubscribeService
    - Show confirmation message
    - _Requirements: US-10_
  
  - [ ] 18.2 Create unsubscribe view
    - Display email being unsubscribed
    - Optional reason dropdown
    - Confirm button
    - Success message
    - Simple, clean design
    - _Requirements: US-10_

- [x] 19. Add routes for all controllers
  - Define admin routes under /event/admin/email-campaigns prefix
  - Apply event.admin middleware
  - Define webhook routes (no auth, signature validation in controller)
  - Define public unsubscribe route
  - Use route names for all routes
  - _Requirements: All_

- [x] 20. Con figure queue and rate limiting
  - [x] 20.1 Configure queue connection
    - Set up Redis queue connection
    - Configure emails queue
    - Set retry_after and timeout
    - _Requirements: AC-3.3_
  
  - [x] 20.2 Configure rate limiting
    - Define email-sending rate limiter (100/minute)
    - Apply to SendEmailBatchJob
    - _Requirements: AC-3.3_
  
  - [x] 20.3 Create queue monitoring command
    - Artisan command to show queue status
    - Display pending jobs, failed jobs
    - _Requirements: AC-3.3_

- [x] 21. Add JavaScript enhancements
  - [x] 21.1 Template editor enhancements
    - TinyMCE initialization with custom toolbar
    - Merge code picker plugin
    - Auto-save draft functionality
    - Preview modal with AJAX
    - _Requirements: AC-1.1_
  
  - [x] 21.2 Campaign creation wizard
    - Multi-step form navigation
    - Real-time recipient count
    - CSV upload with drag-and-drop
    - Column mapping interface
    - _Requirements: AC-2.1, AC-2.2_
  
  - [x] 21.3 Campaign statistics dashboard
    - Real-time statistics updates (polling)
    - Chart.js integration for visualizations
    - Progress bar for sending campaigns
    - _Requirements: AC-2.4_

- [ ] 22. Implement scheduled campaign processing
  - [x] 22.1 Create ProcessScheduledCampaignsCommand
    - Query campaigns with status=scheduled and scheduled_at <= now
    - Call CampaignService.send() for each
    - Log processing
    - _Requirements: AC-2.1_
  
  - [x] 22.2 Schedule command in kernel
    - Run every minute
    - _Requirements: AC-2.1_

- [x] 23. Add event scope isolation test
  - [x]* 23.1 Write property test for event scope isolation
    - **Property 11: Event Scope Isolation**
    - **Validates: Multi-tenancy**

- [x] 24. Final checkpoint - Integration testing
  - [x]* 24.1 Write end-to-end campaign flow test
    - Create template → Create campaign → Upload recipients → Send → Verify delivery
    - Test with multiple providers
    - Test failover scenario
    - _Requirements: All_
  
  - [x]* 24.2 Write webhook integration test
    - Send campaign → Simulate webhooks → Verify tracking
    - _Requirements: AC-2.4_
  
  - [x]* 24.3 Write compliance test
    - Verify unsubscribe link in emails
    - Test unsubscribe flow
    - Verify unsubscribed emails excluded
    - _Requirements: US-10_

- [x] 25. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties (minimum 100 iterations each)
- Unit tests validate specific examples and edge cases
- Integration tests validate end-to-end flows
- Follow Laravel Event Manager patterns: service layer, HasEventScope trait, hash-based routes
- All views must follow UI/UX standards from ui-ux-standards.md
- Provider credentials must be encrypted using Laravel's encryption
- Queue jobs must have retry logic and rate limiting
- All admin routes must be protected by event.admin middleware
