# Barangay Management and Community Services System

A production-ready **Barangay Operations and Community Services Platform** built with:

- **Laravel 12** (PHP 8.2+)
- **Inertia.js** + **Vue 3** (Composition API)
- **Vite** + **Tailwind CSS 4**
- **MySQL / MariaDB**
- **Spatie Laravel Permission** (roles & permissions)
- **Laravel Sanctum** (API auth, ready for future use)
- **mPDF** (PDF generation for documents & analytics reports)

---

## System Overview

The system centralizes barangay operations into one platform:

| Module | Description |
|---|---|
| **Residents** | Resident profiles, verification workflow (Pending → Verified/Rejected), puroks |
| **Households** | Household records with head-of-family, vulnerability indicators, evacuation tracking |
| **Service Requests** | Document requests with tracking numbers (`REQ-YYYY-NNNNNN`), walk-in encoding, printable PDFs |
| **Complaints** | Complaint filing with categories, priorities, assignment, and resolution workflow |
| **Calamities** | Disaster event management with affected puroks |
| **Incidents** | Disaster incident reporting with severity and response tracking |
| **Evacuation** | Evacuation centers, events, and resident registration (time in/out) |
| **Relief** | Inventory with transaction ledger (IN/OUT/ADJUSTMENT) and distribution events |
| **Assistance** | Medical/financial/food assistance requests with approval workflow |
| **Programs** | Government programs (4Ps, TUPAD, etc.) with beneficiary enrollment tracking |
| **Announcements** | Barangay announcements with draft → published → archived lifecycle and priority levels |
| **Census & Reports** | Custom census/report builder with saved definitions and printable analytics PDFs |
| **Audit Logs** | Full audit trail of sensitive operations (user, action, old/new values, IP) |

### Role Hierarchy

```
ADMIN      → Full system access (everything Moderators can do, plus deletes,
             user & role management, barangay settings, audit logs)
MODERATOR  → Member capabilities + staff permissions (verification, processing,
             disaster response, programs, reports, announcement publishing)
MEMBER     → Resident/community capabilities (own requests, complaints, assistance,
             community information)
```

**Key design principle:** A Moderator is still a Member. Moderators have their own
`member_profiles` record and can submit complaints/requests like any resident, while
additionally having staff permissions. There are no separate admin/moderator/member tables —
only `users` + Spatie roles + a 1:1 `member_profiles` link.

> 📋 For a quick-reference matrix of every module and feature per role, see [FEATURES.md](FEATURES.md).

---

## Features by Role

### 👤 Member (Resident)

Members are barangay residents interacting with the barangay office online.

**Dashboard & Communication**
- Personalized dashboard: own request/complaint/assistance summary cards, latest announcements, and notification feed
- **Announcements board**: read all published barangay announcements with type labels (Calamity Warning, Evacuation Notice, Community Event, etc.) and color-coded priorities (Normal / Important / Emergency)
- Unread-announcement badge in the sidebar — automatically clears after visiting the page (no polling)
- Emergency announcements are highlighted with an animated alert banner on the dashboard
- In-app notification bell for status changes on their requests/complaints/assistance

**Community Information**
- View active calamities, ongoing incidents, and the evacuation center directory

**My Requests (Document Requests)**
- Submit document/service requests online
- Track each request with a tracking number (`REQ-YYYY-NNNNNN`) and a full status timeline
  (`submitted → for_verification → approved → processing → ready_for_release → released`)
- Download/print issued documents as PDF once released

**My Complaints**
- File complaints with category, priority, and description
- Track progress via tracking number (`CMP-YYYY-NNNNNN`) through the resolution workflow

**My Assistance**
- Request medical, financial, or food assistance
- Track the approval/status of each assistance request

**Account**
- View personal profile

---

### 🛡️ Moderator (Barangay Staff)

Moderators have **everything Members have**, plus day-to-day staff operations:

**Residents & Households Management**
- Browse, search, and filter residents (by purok, verification status)
- Register new residents and edit profiles
- Verify or reject resident verifications
- Create and maintain household records (head of family, members, vulnerability indicators)
- Evacuate households to an evacuation center during disasters and mark them returned home

**Service Requests Processing**
- Work the request queue with search/filters
- **Walk-in encoding**: create a request on behalf of a resident who visits the office
- Assign, process, approve/reject, encode document details, and release requests
- Preview and print official document PDFs

**Complaints Handling**
- Manage the complaint queue, assign handlers, process investigations,
  conduct mediation, and resolve/close complaints

**Assistance Review**
- Review all assistance requests and update their statuses

**Disaster Response**
- Create/update calamities and tag affected puroks
- Report and update incidents; resolve them when handled
- Manage evacuation centers (create/update) and run evacuation events with resident time-in/time-out registration
- Maintain relief goods inventory (IN/OUT/ADJUSTMENT transaction ledger) and record distribution events

**Programs Management**
- Manage government assistance programs (4Ps, TUPAD, etc.) with codes, budgets, funding agencies, and periods
- Enroll beneficiaries, mark enrollments completed/dropped, and remove beneficiaries

**Announcements Publishing**
- Dedicated **Manage Announcements** screen: compose announcements (title, type, priority, content)
- Save as draft or publish immediately; edit, publish/re-publish, archive, or delete announcements
- Archived announcements disappear from the member-facing board

**Census & Reports**
- Build custom census reports with configurable filters, groupings, and metrics
- Save report definitions for reuse, edit or delete them
- Print analytics reports as PDFs (mPDF)

**Document Types**
- Manage the catalog of requestable document/service types

---

### 🔑 Administrator

Admins have **everything Moderators have**, plus full system control:

- **Delete rights** across modules: residents, households, service requests, complaints, calamities, and evacuation centers
- **Users & Roles**: change any user's role (admin/moderator/member)
- **Barangay Profile & Settings**: maintain barangay information and manage barangay officials records
- **Audit Logs**: view the full audit trail — who did what, when, from which IP, with old/new value diffs
- All announcement, program, report, and disaster-management capabilities of Moderators

---

## Requirements

- PHP 8.2+ (with `pdo_mysql`, `mbstring`, `openssl`, `curl`)
- Composer 2.x
- Node.js 20.19+ or 22.12+ (Vite 7 requirement)
- MySQL 5.7+ / MariaDB 10.3+

---

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment
copy .env.example .env
php artisan key:generate

# 3. Set database credentials in .env
#    DB_DATABASE=barangay_system
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Create the database
mysql -u root -e "CREATE DATABASE barangay_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Run migrations and seeders
php artisan migrate:fresh --seed

# 6. Install frontend dependencies (use Node 20+)
npm install

# 7. Build frontend assets
npm run build

# 8. Start the development server
php artisan serve
```

For live development with hot reload:

```bash
npm run dev          # in one terminal
php artisan serve    # in another
```

---

## Demo Accounts

All demo accounts use the password: **`password`**

| Role | Email | Name |
|---|---|---|
| Admin | `admin@barangay.test` | Admin User |
| Moderator | `maria@barangay.test` | Maria Santos |
| Moderator | `juan@barangay.test` | Juan Dela Cruz |
| Moderator | `ana@barangay.test` | Ana Reyes |
| Member | `pedro.mendoza@barangay.test` | Pedro Garcia Mendoza |

Seed data includes: 5 puroks, 10 households, 20 members, 10 complaints,
10 service requests, 3 assistance requests, 2 calamities, 3 incidents,
3 evacuation centers, 5 inventory items, and 3 announcements.

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/          # Thin controllers (auth, dashboard, residents, requests,
│   │                         # complaints, calamities, disasters, relief, assistance,
│   │                         # programs, announcements, reports, users, settings)
│   └── Middleware/
│       ├── HandleInertiaRequests.php   # Shares auth user/roles/permissions + flash messages
│       └── RoleMiddleware.php          # Role-based route protection
├── Models/                   # 30+ Eloquent models with relationships & soft deletes
├── Notifications/            # Database notifications (request/complaint status changes)
└── Services/
    └── AuditLogService.php   # Centralized audit logging

database/migrations/          # Schema: users, spatie, puroks, households, calamities,
                              # incidents, evacuations, relief, assistance, programs,
                              # announcements, report_definitions, ...
database/seeders/             # RoleAndPermissionSeeder + DatabaseSeeder (demo data)

resources/js/
├── Layouts/                  # GuestLayout, AuthenticatedLayout (permission-driven sidebar)
├── Components/               # StatusBadge, StatCard, Pagination, ReportResults (reusable)
└── Pages/
    ├── Auth/Login.vue
    ├── Dashboard/            # Admin.vue, Moderator.vue, Member.vue (role-specific)
    ├── Residents/            # Index, Create, Show, Edit
    ├── Households/           # Index, Create, Edit
    ├── Requests/             # Index, MyRequests, Create, CreateWalkIn, Show
    ├── Complaints/           # Index, MyComplaints, Create, Show
    ├── Assistance/           # Index
    ├── Calamities/           # Index, Create, Edit
    ├── Incidents/            # Index
    ├── EvacuationCenters/    # Index, Create, Edit
    ├── Relief/               # Inventory, Distributions
    ├── Programs/             # Index, Show
    ├── Announcements/        # Index (member board), Manage (staff CRUD)
    └── Reports/Census/       # Index, Builder, Show
```

### Authorization Model

Permissions are enforced **server-side** at the route level using Laravel's `can:` middleware
and `role:` middleware backed by Spatie permissions. The Vue sidebar dynamically renders
navigation based on the permissions shared via Inertia, but the frontend is never trusted
for authorization.

Key permissions: `view residents`, `verify residents`, `view requests`, `process requests`,
`approve requests`, `view complaints`, `assign complaints`, `process complaints`,
`resolve complaints`, `view calamities`, `manage evacuations`, `manage relief distribution`,
`view reports`, `manage users`, `view audit logs`, etc.

Staff-only screens (programs, census reports, document types, announcement management)
are additionally guarded by `role:admin|moderator` middleware.

### Status Workflows

**Service Requests:** `submitted → for_verification → approved → processing → ready_for_release → released`
(with `rejected` / `cancelled` branches). Every transition is recorded in `request_status_histories`.

**Complaints:** `submitted → under_review → verified → assigned → under_investigation → for_mediation → action_taken → resolved → closed`
(with `rejected` branch). Every transition is recorded in `complaint_status_histories`.

**Announcements:** `draft → published → archived` (announcements can be re-published after archiving).
Only `published` announcements appear on the member board; publishing stamps `published_at`
which drives the unread badge for members.

**Tracking numbers:** Requests use `REQ-YYYY-NNNNNN`, complaints use `CMP-YYYY-NNNNNN`,
calamities use `CAL-YYYY-NNNNNN`, incidents use `INC-YYYY-NNNNNN`.

---

## Testing

An end-to-end smoke test validated the following (13/13 passing):

- Login page renders with Inertia
- Admin/Moderator/Member login and role-specific dashboards
- Admin access to residents/requests/complaints modules
- Member access to own requests
- **Member blocked (403)** from residents and complaints management

Run the Laravel test suite:

```bash
php artisan test
```

---

## Roadmap

Completed: foundation, resident & household management, service requests (incl. walk-in),
complaints, calamities/incidents/evacuations, relief inventory & distribution, assistance
workflow, programs & beneficiary enrollment, announcements lifecycle (draft/publish/archive),
and the census/report builder with PDF printing.

Remaining work:

- **Excel exports** for census/analytics reports (maatwebsite/excel installed, not yet wired)
- **Policies & feature tests** for the newer modules (programs, announcements, reports)
- **Security hardening & UI polish** pass across all modules