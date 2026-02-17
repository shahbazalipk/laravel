# Event Management System - Agenda Module Design

## Overview
Comprehensive agenda management system supporting multiple session types, tracks, locations, speakers, and lectures with proper validation and business rules.

## Entity Structure

### 1. Agenda
Main container for event schedule.

**Fields:**
- id (primary key)
- title (string, required)
- description (text, nullable)
- start_date (date, required)
- end_date (date, required)
- status (enum: draft, published, archived)
- event_id (FK)
- org_id (FK)
- timestamps
- soft deletes

**Relationships:**
- Has many → Tracks
- Has many → Sessions

**Business Rules:**
- end_date must be >= start_date
- Sessions must fall within agenda date range

### 2. Tracks
Thematic groupings for sessions.

**Fields:**
- id (primary key)
- name (string, required)
- description (text, nullable)
- color (string, nullable) - for UI display
- sort_order (integer, default 0)
- agenda_id (FK, required)
- event_id (FK)
- org_id (FK)
- timestamps
- soft deletes

**Relationships:**
- Belongs to → Agenda
- Has many → Sessions

### 3. Locations
Physical or virtual venues.

**Fields:**
- id (primary key)
- name (string, required)
- address (text, nullable)
- room_number (string, nullable)
- capacity (integer, nullable)
- event_id (FK)
- org_id (FK)
- timestamps
- soft deletes

**Relationships:**
- Has many → Sessions
- Has many → Lectures

**Business Rules:**
- Prevent overlapping sessions in same location

### 4. Speakers
Presenters and panelists.

**Fields:**
- id (primary key)
- full_name (string, required)
- bio (text, nullable)
- profile_image (string, nullable)
- email (string, nullable)
- phone (string, nullable)
- company (string, nullable)
- job_title (string, nullable)
- event_id (FK)
- org_id (FK)
- timestamps
- soft deletes

**Relationships:**
- Has many → Lectures
- Many-to-Many → Sessions (via session_speakers pivot)

**Business Rules:**
- Prevent double-booking (same speaker, overlapping times)

### 5. Sessions
Main scheduling unit with type support.

**Fields:**
- id (primary key)
- title (string, required)
- description (text, nullable)
- type (enum: talk, panel, workshop, break, networking, keynote)
- start_time (datetime, required)
- end_time (datetime, required)
- agenda_id (FK, required)
- track_id (FK, nullable - null for breaks)
- location_id (FK, required)
- requires_speakers (boolean, auto false for breaks)
- max_attendees (integer, nullable)
- event_id (FK)
- org_id (FK)
- timestamps
- soft deletes

**Relationships:**
- Belongs to → Agenda
- Belongs to → Track (nullable)
- Belongs to → Location
- Has many → Lectures
- Many-to-Many → Speakers (only if type ≠ break)

**Business Rules:**
- end_time must be > start_time
- Must fall within agenda date range
- Cannot overlap with other sessions in same location
- Track required unless type = break
- Speakers not allowed when type = break
- Lectures not allowed when type = break

### 6. Lectures
Individual presentations within sessions.

**Fields:**
- id (primary key)
- topic (string, required)
- description (text, nullable)
- session_id (FK, required)
- speaker_id (FK, required)
- location_id (FK, required)
- start_time (datetime, required)
- end_time (datetime, required)
- event_id (FK)
- org_id (FK)
- timestamps
- soft deletes

**Relationships:**
- Belongs to → Session
- Belongs to → Speaker
- Belongs to → Location

**Business Rules:**
- Cannot exist if session.type = break
- Speaker required
- Lecture time must be within session time
- Cannot overlap with other lectures by same speaker

### 7. Pivot Table: session_speakers

**Fields:**
- id (primary key)
- session_id (FK)
- speaker_id (FK)
- role (string, nullable) - e.g., "Moderator", "Panelist"
- timestamps

**Constraints:**
- Unique(session_id, speaker_id)
- Not allowed when session.type = break

## Session Types

### talk
- Single speaker presentation
- Requires speakers: Yes
- Track: Required
- Lectures: Allowed

### panel
- Multiple speakers discussion
- Requires speakers: Yes
- Track: Required
- Lectures: Allowed

### workshop
- Interactive session
- Requires speakers: Yes
- Track: Required
- Lectures: Allowed

### break
- Coffee break, lunch, etc.
- Requires speakers: No
- Track: Optional
- Lectures: Not allowed

### networking
- Networking session
- Requires speakers: No
- Track: Optional
- Lectures: Allowed (optional)

### keynote
- Main presentation
- Requires speakers: Yes
- Track: Optional
- Lectures: Allowed

## Validation Rules

### Session Validation
1. Time validation:
   - end_time > start_time
   - Within agenda date range
   - No location conflicts

2. Type-specific validation:
   - break: no speakers, no lectures, track optional
   - Other types: speakers allowed, track required

3. Speaker validation:
   - No double-booking
   - Speaker must exist

### Lecture Validation
1. Must belong to valid session
2. Session type cannot be "break"
3. Time must be within session time
4. Speaker cannot be double-booked
5. Location must match or be compatible

## API Endpoints

### Agendas
- GET /admin/agendas - List all
- POST /admin/agendas - Create
- GET /admin/agendas/{id} - Show
- PUT /admin/agendas/{id} - Update
- DELETE /admin/agendas/{id} - Delete

### Tracks
- GET /admin/tracks - List all
- POST /admin/tracks - Create
- GET /admin/tracks/{id} - Show
- PUT /admin/tracks/{id} - Update
- DELETE /admin/tracks/{id} - Delete

### Locations
- GET /admin/locations - List all
- POST /admin/locations - Create
- GET /admin/locations/{id} - Show
- PUT /admin/locations/{id} - Update
- DELETE /admin/locations/{id} - Delete

### Speakers
- GET /admin/speakers - List all
- POST /admin/speakers - Create
- GET /admin/speakers/{id} - Show
- PUT /admin/speakers/{id} - Update
- DELETE /admin/speakers/{id} - Delete

### Sessions
- GET /admin/sessions - List all
- POST /admin/sessions - Create
- GET /admin/sessions/{id} - Show
- PUT /admin/sessions/{id} - Update
- DELETE /admin/sessions/{id} - Delete
- POST /admin/sessions/{id}/speakers - Attach speaker
- DELETE /admin/sessions/{id}/speakers/{speaker_id} - Detach speaker

### Lectures
- GET /admin/lectures - List all
- POST /admin/lectures - Create
- GET /admin/lectures/{id} - Show
- PUT /admin/lectures/{id} - Update
- DELETE /admin/lectures/{id} - Delete

## Services

### AgendaService
- createAgenda()
- updateAgenda()
- deleteAgenda()
- validateDateRange()

### SessionService
- createSession()
- updateSession()
- deleteSession()
- validateSessionTime()
- checkLocationConflict()
- attachSpeaker()
- detachSpeaker()
- validateSpeakerForType()

### LectureService
- createLecture()
- updateLecture()
- deleteLecture()
- validateLectureTime()
- checkSpeakerConflict()

### SpeakerService
- createSpeaker()
- updateSpeaker()
- deleteSpeaker()
- checkAvailability()

## Implementation Order

1. ✅ Migrations (all entities)
2. ✅ Models with relationships
3. ✅ Services with business logic
4. ✅ Controllers
5. ✅ Routes
6. ✅ Views (CRUD interfaces)
7. ✅ Seeders
8. ✅ Validation
9. ✅ Tests

## UI Features

### Agenda Management
- Calendar view
- List view
- Drag-and-drop scheduling
- Conflict detection
- Color-coded tracks

### Session Management
- Type selector
- Speaker assignment
- Location booking
- Time slot picker
- Conflict warnings

### Speaker Management
- Profile management
- Availability calendar
- Session assignments
- Bio and photo upload

## Notes
- All entities use HasEventScope trait for multi-tenancy
- All entities use HasHashedRoutes for URL security
- Soft deletes enabled on all entities
- Audit logging via HasAuditLogging trait
