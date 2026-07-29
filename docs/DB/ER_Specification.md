# EventDNA — ER Diagram Specification (Revision 2)

Derived from the SCS2202 / IS2102 Group 56 Project Proposal, updated with two system-flow decisions: constant event QR with registered-list validation, and first-time organizer verification.

Naming convention: `snake_case`, singular table names.

---

## 1. System Flow Changes in This Revision

### 1.1 Event check-in: constant QR, registered-list validation

Each event generates a single, constant QR code at creation time. This code does not rotate and does not expire for the lifetime of the event. It is stored as `event.event_qr_token`. There is no separate expiry timestamp for it.

The backend keeps the registered list for an event in the `registration` table. When a check-in scan comes in for an event and a user, the backend checks that a row exists in `registration` for that exact `attendee_id` and `event_id`. Only if that row exists does the system set `attendance_status` to `checked_in`. If no such row exists, the check-in is rejected and the person is directed to register first.

**Design note:** Because the token is constant, security depends entirely on the registered-list check, not on token expiry. This is why the check-in path always hits the `registration` table on every scan.

### 1.2 Role switching: attendee to organizer

A user who registers as an Attendee can later switch to the Organizer role — a normal, repeatable action once cleared.

The **first time** a user attempts to switch to Organizer, the system does not grant the role immediately. It creates a row in `organizer_verification_request` capturing identity and contact details, and the request sits in `pending` status until an admin approves or rejects it. Only on approval does the system set `user.is_organizer_verified` to `TRUE` and update `user.role` to `organizer`.

After that first approval, the user can freely switch between Attendee and Organizer (a plain update to `user.role`) with no further approval step.

**Design note:** `is_organizer_verified` is a one-time gate, not a per-switch check. Keeping it as a boolean on `user` keeps every later role switch a single cheap update instead of a repeated lookup.

---

## 2. Entities

| # | Entity | Purpose |
|---|--------|---------|
| 1 | `user` | Base entity for all three roles (Attendee, Organizer, Administrator); auth, RBAC, role switching |
| 2 | `skill` | Lookup table — normalized so the matching engine can compare across users (FR-16) |
| 3 | `interest` | Lookup table — same reasoning as skill; drives interest-distribution analytics (FR-28) and group tags |
| 4 | `networking_goal` | Lookup table of goal types (e.g. "find mentor"); needed for goal-pairing (FR-08, FR-16) |
| 5 | `user_skill` | Junction resolving user M:N skill |
| 6 | `user_interest` | Junction resolving user M:N interest |
| 7 | `user_goal` | Junction resolving user M:N networking_goal; carries priority ordering (FR-08) |
| 8 | `event` | Organizer-created events with schedule, window, visibility, capacity, constant QR token (FR-09–12) |
| 9 | `registration` | Associative entity resolving user M:N event; the registered list checked on every QR scan; carries check-in state (FR-13–15, UC-16) |
| 10 | `connection` | Full connection lifecycle between two attendees: pending, accepted, rejected, removed (FR-22) |
| 11 | `community_group` | Interest-based groups in the Community Hub (FR-23) |
| 12 | `group_membership` | Junction resolving user M:N community_group; "members only" visibility (Section 7.1) |
| 13 | `group_interest` | Junction resolving community_group M:N interest (group interest tags, UC-31) |
| 14 | `thread` | A discussion topic inside a group (FR-24) |
| 15 | `reply` | A reply within a thread (FR-24) |
| 16 | `resource` | A file or link shared inside a group (FR-24) |
| 17 | `notification` | In-app notifications per user; email is a delivery channel, not a separate entity (FR-25) |
| 18 | `password_reset_token` | Time-limited tokenized reset links (FR-04). Weak entity, see Section 5 |
| 19 | `opportunity` | Opportunity recommendations (e.g. internships) shown to checked-in attendees (FR-19) |
| 20 | `match_log` | Log of computed compatibility scores and suggested matches, written by the matching engine |
| 21 | `moderation_log` | Audit trail of admin moderation actions with timestamp and reason (FR-26, UC-34) |
| 22 | `organizer_verification_request` | *(new)* Identity/affiliation details submitted the first time an attendee requests Organizer role, plus admin decision |

---

## 3. Attributes per Entity

### user
- `user_id` — INT — PK, AUTO_INCREMENT
- `full_name` — VARCHAR(100) — NOT NULL
- `email` — VARCHAR(255) — NOT NULL, UNIQUE
- `password_hash` — VARCHAR(255) — NOT NULL — bcrypt hash only (FR-02)
- `role` — ENUM('attendee','organizer','admin') — NOT NULL, DEFAULT 'attendee' — current active role, switchable
- `is_organizer_verified` — BOOLEAN — NOT NULL, DEFAULT FALSE — set TRUE once first verification request approved
- `account_status` — ENUM('pending','active','disabled') — NOT NULL, DEFAULT 'pending'
- `email_verify_token` — VARCHAR(64) — NULL, UNIQUE — cleared after activation
- `personal_qr_token` — VARCHAR(64) — NULL, UNIQUE — token in personal QR public-profile URL (FR-20)
- `profile_photo_path` — VARCHAR(255) — NULL — uploaded JPEG/PNG (FR-07)
- `bio` — TEXT — NULL
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW
- `updated_at` — DATETIME — NOT NULL, DEFAULT NOW, ON UPDATE NOW — added in the physical schema, see Section 7.3

*Derived (not stored): profile completeness % — used when a match list is too sparse to score reliably.*

### skill
- `skill_id` — INT — PK, AUTO_INCREMENT
- `skill_name` — VARCHAR(80) — NOT NULL, UNIQUE

### interest
- `interest_id` — INT — PK, AUTO_INCREMENT
- `interest_name` — VARCHAR(80) — NOT NULL, UNIQUE

### networking_goal
- `goal_id` — INT — PK, AUTO_INCREMENT
- `goal_name` — VARCHAR(80) — NOT NULL, UNIQUE — e.g. find_mentor, find_collaborator, hire, be_mentored

### user_skill
- `user_id` — INT — PK part, FK → user
- `skill_id` — INT — PK part, FK → skill
- Composite PK (user_id, skill_id)

### user_interest
- `user_id` — INT — PK part, FK → user
- `interest_id` — INT — PK part, FK → interest

### user_goal
- `user_id` — INT — PK part, FK → user
- `goal_id` — INT — PK part, FK → networking_goal
- `priority` — TINYINT — NOT NULL — 1 = highest (FR-08)

### event
- `event_id` — INT — PK, AUTO_INCREMENT
- `organizer_id` — INT — FK → user, NOT NULL — RBAC restricts edits to owner or admin
- `title` — VARCHAR(150) — NOT NULL
- `description` — TEXT — NULL
- `location` — VARCHAR(255) — NOT NULL
- `start_datetime` — DATETIME — NOT NULL
- `end_datetime` — DATETIME — NOT NULL — must be after start (UC-08 validation)
- `reg_open` — DATETIME — NOT NULL
- `reg_close` — DATETIME — NOT NULL — must precede event start
- `visibility` — ENUM('public','private') — NOT NULL, DEFAULT 'public' (FR-10)
- `capacity` — INT — NOT NULL — positive cap (FR-10)
- `event_status` — ENUM('active','cancelled','completed') — NOT NULL, DEFAULT 'active'
- `event_qr_token` — VARCHAR(64) — NOT NULL, UNIQUE — constant, generated once at creation, never rotated
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW

### registration
*(associative: user M:N event; also the registered list used for QR validation)*
- `registration_id` — INT — PK, AUTO_INCREMENT — surrogate; (attendee_id, event_id) also UNIQUE
- `attendee_id` — INT — FK → user, NOT NULL
- `event_id` — INT — FK → event, NOT NULL
- `registered_at` — DATETIME — NOT NULL, DEFAULT NOW
- `attendance_status` — ENUM('registered','checked_in') — NOT NULL, DEFAULT 'registered'
- `checked_in_at` — DATETIME — NULL — set on first successful scan; idempotent on repeat

### connection
- `connection_id` — INT — PK, AUTO_INCREMENT
- `requester_id` — INT — FK → user, NOT NULL
- `recipient_id` — INT — FK → user, NOT NULL — UNIQUE(requester_id, recipient_id) pair (app-enforced, either direction)
- `event_id` — INT — FK → event, NULL — event where the connection was initiated
- `connection_status` — ENUM('pending','accepted','rejected','removed') — NOT NULL, DEFAULT 'pending'
- `requested_at` — DATETIME — NOT NULL, DEFAULT NOW
- `responded_at` — DATETIME — NULL

### community_group
- `group_id` — INT — PK, AUTO_INCREMENT
- `group_name` — VARCHAR(100) — NOT NULL, UNIQUE
- `description` — TEXT — NULL
- `created_by` — INT — FK → user, NOT NULL — creator becomes first member
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW

### group_membership
- `user_id` — INT — PK part, FK → user
- `group_id` — INT — PK part, FK → community_group
- `joined_at` — DATETIME — NOT NULL, DEFAULT NOW
- `membership_status` — ENUM('active','removed') — NOT NULL, DEFAULT 'active'

### group_interest
- `group_id` — INT — PK part, FK → community_group
- `interest_id` — INT — PK part, FK → interest

### thread
- `thread_id` — INT — PK, AUTO_INCREMENT
- `group_id` — INT — FK → community_group, NOT NULL
- `author_id` — INT — FK → user, NOT NULL
- `title` — VARCHAR(150) — NOT NULL
- `body` — TEXT — NOT NULL
- `is_removed` — BOOLEAN — NOT NULL, DEFAULT FALSE — soft delete (UC-35)
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW

### reply
- `reply_id` — INT — PK, AUTO_INCREMENT
- `thread_id` — INT — FK → thread, NOT NULL
- `author_id` — INT — FK → user, NOT NULL
- `body` — TEXT — NOT NULL
- `is_removed` — BOOLEAN — NOT NULL, DEFAULT FALSE
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW

### resource
- `resource_id` — INT — PK, AUTO_INCREMENT
- `group_id` — INT — FK → community_group, NOT NULL
- `uploaded_by` — INT — FK → user, NOT NULL
- `title` — VARCHAR(150) — NOT NULL
- `file_path` — VARCHAR(255) — NOT NULL — or external URL
- `uploaded_at` — DATETIME — NOT NULL, DEFAULT NOW

### notification
- `notification_id` — INT — PK, AUTO_INCREMENT
- `user_id` — INT — FK → user, NOT NULL — recipient
- `notif_type` — ENUM('event_update','event_cancelled','connection_request','connection_accepted','moderation','organizer_request_reviewed','system') — NOT NULL
- `message` — VARCHAR(500) — NOT NULL
- `link_url` — VARCHAR(255) — NULL
- `is_read` — BOOLEAN — NOT NULL, DEFAULT FALSE
- `email_sent` — BOOLEAN — NOT NULL, DEFAULT FALSE — tracks SMTP dispatch (FR-25)
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW

### password_reset_token
*(weak entity — see Section 5)*
- `token_hash` — VARCHAR(64) — partial key (unique within owning user)
- `user_id` — INT — FK → user, PK part — identifying relationship
- `expires_at` — DATETIME — NOT NULL (FR-04)
- `used` — BOOLEAN — NOT NULL, DEFAULT FALSE

### opportunity
- `opportunity_id` — INT — PK, AUTO_INCREMENT
- `event_id` — INT — FK → event, NULL — optionally scoped
- `posted_by` — INT — FK → user, NOT NULL
- `title` — VARCHAR(150) — NOT NULL
- `description` — TEXT — NOT NULL
- `opportunity_type` — ENUM('internship','job','collaboration','mentorship','other') — NOT NULL
- `created_at` — DATETIME — NOT NULL, DEFAULT NOW

*Note: relevance is matched via interests/skills. Add `opportunity_skill` / `opportunity_interest` junctions if tag-based matching is wanted over text matching.*

### match_log
- `match_log_id` — INT — PK, AUTO_INCREMENT
- `event_id` — INT — FK → event, NOT NULL
- `user_a_id` — INT — FK → user, NOT NULL — attendee viewing matches
- `user_b_id` — INT — FK → user, NOT NULL — suggested candidate
- `compatibility_score` — DECIMAL(5,2) — NOT NULL — weighted sum output (FR-16)
- `computed_at` — DATETIME — NOT NULL, DEFAULT NOW

### moderation_log
- `mod_log_id` — INT — PK, AUTO_INCREMENT
- `admin_id` — INT — FK → user, NOT NULL
- `action_type` — ENUM('remove_post','remove_reply','remove_member','disable_account','edit_event','cancel_event') — NOT NULL
- `target_type` — VARCHAR(30) — NOT NULL — entity type acted on
- `target_id` — INT — NOT NULL
- `reason` — VARCHAR(255) — NULL
- `logged_at` — DATETIME — NOT NULL, DEFAULT NOW

### organizer_verification_request *(new)*
- `request_id` — INT — PK, AUTO_INCREMENT
- `user_id` — INT — FK → user, NOT NULL — attendee requesting Organizer role
- `full_legal_name` — VARCHAR(150) — NOT NULL
- `organization_name` — VARCHAR(150) — NULL
- `contact_number` — VARCHAR(20) — NOT NULL
- `id_document_path` — VARCHAR(255) — NOT NULL — uploaded proof of identity/affiliation
- `justification` — TEXT — NULL
- `request_status` — ENUM('pending','approved','rejected') — NOT NULL, DEFAULT 'pending'
- `reviewed_by` — INT — FK → user, NULL — reviewing admin
- `review_notes` — VARCHAR(255) — NULL
- `submitted_at` — DATETIME — NOT NULL, DEFAULT NOW
- `reviewed_at` — DATETIME — NULL

---

## 4. Relationships

| # | Name | Entities | Cardinality | Participation | Notes |
|---|------|----------|-------------|----------------|-------|
| R1 | organizes | user (organizer) → event | 1:N | user partial, event total | Every event has exactly one owner |
| R2 | has skill | user → skill | M:N | both partial | Resolved by user_skill |
| R3 | has interest | user → interest | M:N | both partial | Resolved by user_interest |
| R4 | prioritizes goal | user → networking_goal | M:N | both partial | Resolved by user_goal (attr: priority) |
| R5 | registers for | user (attendee) → event | M:N | both partial | Resolved by registration; also the registered list checked on QR scan |
| R6 | requests connection | user → user (recursive) | M:N | partial both sides | Resolved by connection; requester/recipient roles; optional FK to event |
| R7 | initiated at | connection → event | N:1 | connection partial, event partial | Which event the match came from |
| R8 | creates | user → community_group | 1:N | user partial, group total | |
| R9 | member of | user → community_group | M:N | both partial | Resolved by group_membership |
| R10 | tagged with | community_group → interest | M:N | both partial | Resolved by group_interest |
| R11 | contains | community_group → thread | 1:N | group partial, thread total | |
| R12 | authors | user → thread | 1:N | user partial, thread total | |
| R13 | has reply | thread → reply | 1:N | thread partial, reply total | |
| R14 | writes | user → reply | 1:N | user partial, reply total | |
| R15 | shares | community_group → resource | 1:N | group partial, resource total | uploaded_by FK also links to user |
| R16 | receives | user → notification | 1:N | user partial, notification total | |
| R17 | requests reset for | user → password_reset_token | 1:N | user partial, token total | Identifying relationship (weak entity) |
| R18 | posts | user → opportunity | 1:N | user partial, opportunity total | Optional N:1 to event as well |
| R19 | scored in | match_log → (event, user x2) | N:1 each | match_log total on all three FKs | Pure log/analytics entity |
| R20 | performs | user (admin) → moderation_log | 1:N | user partial, log total | |
| R21 | submits | user (attendee) → organizer_verification_request | 1:N | user partial, request total | New — only needed the first time a user seeks Organizer role |
| R22 | reviews | user (admin) → organizer_verification_request | 1:N | admin partial, request partial | New — reviewed_by FK; request stays pending until an admin acts |

*M:N resolutions already listed as entities: `user_skill`, `user_interest`, `user_goal`, `registration`, `connection`, `group_membership`, `group_interest`, `organizer_verification_request`.*

---

## 5. Weak Entities

- **`password_reset_token`** — no meaningful standalone identity; identified by (user_id, token_hash) where token_hash is the partial key and "requests reset for" (R17) is the identifying relationship.
- Everything else uses surrogate PKs — no other strict weak entities. `organizer_verification_request` has its own surrogate key (`request_id`) even though tied to one user, so it's a normal strong entity, not weak.

---

## 6. Assumptions

- Email verification and personal QR tokens are stored as columns on `user` rather than separate entities, since each user has at most one active value of each.
- The event QR token is treated as constant for the full lifetime of the event; validation on every scan is a membership check against `registration`, not a token expiry check — deliberately no `qr_token_expires_at` column.
- Emails are a delivery channel of `notification`, not their own entity.
- Connection uniqueness across both directions (A→B blocks B→A) is enforced in application logic, since MySQL cannot express a symmetric unique constraint declaratively.
- Opportunities are assumed to be posted by organizers or admins and matched to attendees via profile data.
- Only the first attendee-to-organizer switch requires review through `organizer_verification_request`; once `is_organizer_verified` is TRUE, later switches are a plain update to `user.role`.
- Any Administrator may review `organizer_verification_request`, matching the existing admin oversight pattern used for `moderation_log`.

---

## 7. Physical Schema Decisions

Decisions made while writing the DDL under `docs/DB/schema/` that this ER document did not specify. Recorded here so the logical spec and the physical schema stay in sync.

### 7.1 Global conventions

The whole schema lives in a single file, `docs/DB/schema/eventdna_schema.sql`, which creates the database `eventdna` with `utf8mb4` / `utf8mb4_unicode_ci` as its defaults, then creates all 22 tables in dependency order. It drops in reverse order first, so it can be re-run from scratch.

All tables: `ENGINE=InnoDB`, `DEFAULT CHARSET=utf8mb4`, `COLLATE=utf8mb4_unicode_ci`. The spec's "DEFAULT NOW" is realised as `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP`. Row format is left at the server default, which is `DYNAMIC` on the target (MariaDB 10.4).

Note that `utf8mb4_unicode_ci` is case-insensitive, so the `UNIQUE` constraints on `skill_name`, `interest_name`, and `goal_name` treat "PHP" and "php" as the same value. This is intended — the lookup tables exist to give the matching engine one canonical row per concept.

### 7.2 Referential actions

The spec declares no `ON DELETE` / `ON UPDATE` behaviour. Adopted policy: **`ON DELETE CASCADE`** on the profile junction tables (`user_skill`, `user_interest`, `user_goal`), on both the `user` side and the lookup side.

No `ON UPDATE` action is declared. Every PK in this schema is an `AUTO_INCREMENT` surrogate that is never reassigned, so an update to cascade cannot occur.

Rationale: admin account management includes hard delete, not only `account_status = 'disabled'`, so deleting a user must not strand profile rows. Cascade from the lookup side means retiring a skill also removes it from every profile that claimed it.

Across the rest of the schema the action is chosen per FK rather than applied uniformly:

| Action | Foreign keys | Reason |
|---|---|---|
| `CASCADE` | all junctions, `password_reset_token`, `registration`, `connection` (both user sides), `thread`, `reply`, `resource`, `group_membership`, `group_interest`, `notification`, `opportunity.posted_by`, all three `match_log` FKs | Row has no meaning without its parent, or is cheaply regenerated |
| `RESTRICT` | `event.organizer_id`, `community_group.created_by`, `moderation_log.admin_id` | Deleting the parent would destroy work belonging to other users, or break an audit trail. Forces the admin to reassign or cancel first |
| `SET NULL` | `organizer_verification_request.reviewed_by`, `connection.event_id`, `opportunity.event_id` | Column is already nullable and the row stays meaningful without it |

`match_log` cascades despite being a log, because its scores are recomputed by the matching engine rather than being a record of fact. `moderation_log` restricts, because it is.

Note the practical consequence of `RESTRICT`: a user who owns an event or created a group cannot be deleted until those are reassigned. `account_status = 'disabled'` remains the normal deactivation path.

### 7.3 Additions not present in Section 3

| Addition | Table | Reason |
|---|---|---|
| `updated_at` column | `user` | Admin user-management CRUD needs a last-modified timestamp; `moderation_log` only covers admin-initiated changes, not self-service profile edits |
| `UNIQUE (user_id, priority)` | `user_goal` | Section 3 says "1 = highest" but does not forbid ties. Enforcing a strict ordering gives FR-08 goal-pairing an unambiguous top goal |
| `INDEX (role, account_status)` | `user` | Backs the admin manage-users list, which filters and sorts on both columns |
| Reverse-side indexes | all junctions | A composite PK indexes only its leading column. The matching engine's "who else has skill X" queries need `skill_id` / `interest_id` / `goal_id` indexed independently |
| `INDEX (token_hash)` | `password_reset_token` | The reset link carries only the token, so the lookup arrives without a `user_id` and cannot use the composite PK. See 7.5 |
| `CHECK` constraints | `event`, `connection`, `match_log` | Encode rules Section 3 already states in prose: end after start, reg window inside the event, positive capacity, no self-connection, no self-match |
| Query indexes | `event`, `registration`, `connection`, `thread`, `reply`, `resource`, `notification`, `moderation_log`, `organizer_verification_request` | Event browse, attendee lists, notification inbox, moderation queue. None are in Section 3, which lists no indexes at all |

### 7.4 Spec inconsistency noted

Section 3 states "Composite PK (user_id, skill_id)" under `user_skill` but omits the equivalent line under `user_interest` and `user_goal`. Treated as an editorial omission — all three junctions use a composite PK over their two FK columns.

### 7.5 Open issue — `password_reset_token` identity

Section 5 models this as a weak entity with `token_hash` as a partial key, unique only within the owning user. The physical schema follows that: `PRIMARY KEY (user_id, token_hash)`.

This does not match how the table is actually queried. A password reset link contains the token and nothing else — the user is not logged in and no `user_id` is available. So the lookup is `WHERE token_hash = ?`, which cannot use a `user_id`-leading composite PK. A separate `INDEX (token_hash)` has been added to make that query work.

The weak-entity model is also weaker than it should be for security. A token that is only unique *within* a user means two users can hold the same token value, and the reset endpoint would then have to disambiguate them. The token should be globally unique.

**Recommended change to Section 5:** treat `password_reset_token` as a strong entity with `token_hash` as its sole primary key, or keep the composite PK and add `UNIQUE (token_hash)`. Either makes the token globally unique and the lookup index-backed. Left as-is pending a decision, since it changes the entity classification in Section 5.
