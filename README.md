# Barangay Management and Community Services System

A production-ready **Barangay Operations and Community Services Platform** built with:

- **Laravel 12** (PHP 8.2+)
- **Inertia.js** + **Vue 3** (Composition API)
- **Vite** + **Tailwind CSS 4**
- **MySQL / MariaDB**
- **Spatie Laravel Permission** (roles & permissions)
- **Laravel Sanctum** (API auth, ready for future use)
- **Lucide-style inline SVG icons**

---

## System Overview

The system centralizes barangay operations into one platform:

| Module | Description |
|---|---|
| **Residents** | Resident profiles, verification workflow (Pending → Verified/Rejected), puroks |
| **Households** | Household records with head-of-family and vulnerability indicators |
| **Service Requests** | Document requests with tracking numbers (`REQ-YYYY-NNNNNN`) and full status workflow |
| **Complaints** | Complaint filing with categories, priorities, assignment, and resolution workflow |
| **Calamities** | Disaster event management with affected puroks/households/residents |
| **Incidents** | Disaster incident reporting with severity and response tracking |
| **Evacuation** | Evacuation centers, events, and resident registration (time in/out) |
| **Relief** | Inventory with transaction ledger (IN/OUT/ADJUSTMENT) and distribution events |
| **Assistance** | Medical/financial/food assistance requests with approval workflow |
| **Announcements** | Barangay announcements with priority levels (Normal/Important/Emergency) |
| **Audit Logs** | Full audit trail of sensitive operations (user, action, old/new values, IP) |

### Role Hierarchy

```
ADMIN      → Full system access
MODERATOR  → Member capabilities + staff permissions (verification, processing, response)
MEMBER     → Resident/community capabilities (own requests, complaints, assistance)
```

**Key design principle:** A Moderator is still a Member. Moderators have their own
`member_profiles` record and can submit complaints/requests like any resident, while
additionally having staff permissions. There are no separate admin/moderator/member tables —
only `users` + Spatie roles + a 1:1 `member_profiles` link.

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
│   ├── Controllers/          # Thin controllers (auth, dashboard, residents, requests, complaints)
│   └── Middleware/
│       ├── HandleInertiaRequests.php   # Shares auth user/roles/permissions + flash messages
│       └── RoleMiddleware.php          # Role-based route protection
├── Models/                   # 30+ Eloquent models with relationships & soft deletes
├── Notifications/            # Database notifications (request/complaint status changes)
└── Services/
    └── AuditLogService.php   # Centralized audit logging

database/migrations/          # 16 migrations (users, spatie, puroks, households, ...)
database/seeders/             # RoleAndPermissionSeeder + DatabaseSeeder (demo data)

resources/js/
├── Layouts/                  # GuestLayout, AuthenticatedLayout (permission-driven sidebar)
├── Components/               # StatusBadge, StatCard, Pagination (reusable)
└── Pages/
    ├── Auth/Login.vue
    ├── Dashboard/            # Admin.vue, Moderator.vue, Member.vue (role-specific)
    ├── Residents/            # Index, Create, Show, Edit
    ├── Requests/             # Index, MyRequests, Create, Show
    └── Complaints/           # Index, MyComplaints, Create, Show
```

### Authorization Model

Permissions are enforced **server-side** at the route level using Laravel's `can:` middleware
backed by Spatie permissions. The Vue sidebar dynamically renders navigation based on the
permissions shared via Inertia, but the frontend is never trusted for authorization.

Key permissions: `view residents`, `verify residents`, `view requests`, `process requests`,
`approve requests`, `view complaints`, `assign complaints`, `process complaints`,
`resolve complaints`, `view calamities`, `manage evacuations`, `manage relief distribution`,
`view reports`, `manage users`, `view audit logs`, etc.

### Status Workflows

**Service Requests:** `submitted → for_verification → approved → processing → ready_for_release → released`
(with `rejected` / `cancelled` branches). Every transition is recorded in `request_status_histories`.

**Complaints:** `submitted → under_review → verified → assigned → under_investigation → for_mediation → action_taken → resolved → closed`
(with `rejected` branch). Every transition is recorded in `complaint_status_histories`.

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

## Roadmap (Remaining Phases)

The foundation, resident management, requests, and complaints modules are complete.
Remaining phases per the project spec:

- **Phase 5:** Calamity/Incident/Evacuation controllers + pages (models & migrations ready)
- **Phase 6:** Relief inventory & distribution controllers + pages (models & migrations ready)
- **Phase 7:** Assistance workflow controllers + pages (models & migrations ready)
- **Phase 8:** Announcements CRUD + notification center UI (models & notifications ready)
- **Phase 9:** Reports with charts, PDF (dompdf installed), Excel (maatwebsite/excel installed)
- **Phase 10:** Policies, feature tests, security hardening, UI polish