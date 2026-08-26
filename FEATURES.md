# Feature Guide — Modules & Features by Role

A quick-reference guide to every module in the **Barangay Management and Community Services System**,
organized by user role for easy understanding.

> Roles are cumulative: an **Admin** can do everything a **Moderator** can, and a **Moderator**
> can do everything a **Member** can. See the [README](README.md) for installation and architecture.

---

## 1. Role Legend

| Icon | Role | Who |
|---|---|---|
| 👤 | **Member** | Barangay residents using the system online |
| 🛡️ | **Moderator** | Barangay staff handling day-to-day operations |
| 🔑 | **Administrator** | Full system control |

---

## 2. Module × Role Matrix (At a Glance)

✅ = full access · 👁️ = view only · ❌ = no access

| # | Module | 👤 Member | 🛡️ Moderator | 🔑 Admin |
|---|---|:---:|:---:|:---:|
| 1 | Dashboard | ✅ Personal | ✅ Staff view | ✅ Full analytics |
| 2 | Announcements (view board) | ✅ | ✅ | ✅ |
| 3 | Announcements (manage/publish) | ❌ | ✅ | ✅ |
| 4 | My Requests (own document requests) | ✅ | ✅ | ✅ |
| 5 | Service Requests (all residents') | ❌ | ✅ | ✅ |
| 6 | Walk-in Request Encoding | ❌ | ✅ | ✅ |
| 7 | My Complaints (own complaints) | ✅ | ✅ | ✅ |
| 8 | Complaints Management (all) | ❌ | ✅ | ✅ |
| 9 | My Assistance (own requests) | ✅ | ✅ | ✅ |
| 10 | Assistance Review (all requests) | ❌ | ✅ | ✅ |
| 11 | Residents Directory | ❌ | ✅ | ✅ |
| 12 | Resident Verification | ❌ | ✅ | ✅ |
| 13 | Delete Residents | ❌ | ❌ | ✅ |
| 14 | Households | ❌ | ✅ (no delete) | ✅ (incl. delete) |
| 15 | Calamities | 👁️ Active list | ✅ Create/Update | ✅ Full CRUD |
| 16 | Incidents | 👁️ Active list | ✅ Report/Resolve | ✅ Report/Resolve |
| 17 | Incident Blotters (blotter logbook) | ❌ | ✅ | ✅ |
| 18 | Evacuation Centers | 👁️ Directory | ✅ Manage + Evacuations | ✅ Full CRUD |
| 19 | Relief Inventory & Distribution | ❌ | ✅ | ✅ |
| 20 | Programs & Beneficiaries | ❌ | ✅ | ✅ |
| 21 | Census & Report Builder | ❌ | ✅ | ✅ |
| 22 | Document Types | ❌ | ✅ | ✅ |
| 23 | Users & Roles | ❌ | ❌ | ✅ |
| 24 | Barangay Profile & Officials | ❌ | ❌ | ✅ |
| 25 | Audit Logs | ❌ | ❌ | ✅ |
| 26 | Notifications | ✅ | ✅ | ✅ |
| 27 | Profile | ✅ | ✅ | ✅ |

---

## 3. Detailed Features per Role

### 👤 Member (Resident)

#### Dashboard
- Personal summary cards: my requests, my complaints, my assistance
- Latest announcements feed
- **Emergency alert banner** — animated alarm for emergency-priority announcements (flood warnings, etc.)
- Notification feed of status changes

#### Announcements Board (`/announcements`)
- Read all **published** announcements
- Each card shows title, date published, type label (Calamity Warning, Evacuation Notice,
  Community Event, Service Interruption, Emergency Instruction, General), and color-coded priority
- Sidebar badge shows how many announcements were posted since the last visit; clears automatically on visit

#### Community Information
- View active calamities
- View ongoing incidents (with purok info)
- View evacuation center directory (name, capacity, occupancy, status)

#### My Requests (`/my-requests`)
- Submit document/service requests online
- Track each request via tracking number `REQ-YYYY-NNNNNN`
- View full status timeline: `submitted → for_verification → approved → processing → ready_for_release → released`
- Download/print the issued document as PDF once released

#### My Complaints (`/my-complaints`)
- File complaints with category, priority, description
- Track progress via tracking number `CMP-YYYY-NNNNNN`
- Follow the resolution workflow timeline

#### My Assistance (`/my-assistance`)
- Request medical, financial, or food assistance
- Track approval/status of each request

#### Account
- View personal profile
- In-app notification bell (request/complaint/assistance status changes)

---

### 🛡️ Moderator (Barangay Staff)

*Includes everything a Member can do, plus:*

#### Residents Management (`/residents`)
- Browse, search, filter by purok and verification status
- Register new residents; edit profiles
- **Verify** or **reject verification** of resident accounts

#### Households Management (`/households`)
- Create and edit household records (household code, head of family, members, vulnerability indicators)
- **Evacuate** a household to an evacuation center during disasters
- Mark households as **returned home**

#### Service Requests Processing (`/requests`)
- Work the full request queue with search/filters
- **Walk-in encoding**: create a request on behalf of a resident at the office (`/requests/create`)
- Assign a processor, process, approve/reject, encode document details, release
- Preview and print official document PDFs

#### Complaints Handling (`/complaints`)
- Manage all complaints
- Assign handlers, process investigations, conduct mediation, resolve/close

#### Assistance Review (`/assistance`)
- View all assistance requests from members
- Update request statuses

#### Disaster Response
- **Calamities** (`/calamities`): create/update calamities, tag affected puroks
- **Incidents** (`/incidents`): report incidents, update, resolve; quick **"Record Blotter Entry"** button
- **Incident Blotters** (`/incidents/blotters`): official barangay blotter logbook
  - Record incidents into the blotter with entry type (accident, animal incident,
    disturbance, theft, dispute, property damage, other), title, detailed narrative,
    location/purok, and date & time of occurrence
  - Capture complainant/reporter name & contact number, persons/parties involved,
    injuries-reported flag, actions taken, and remarks
  - Optionally link a blotter entry to an existing active incident report
  - Auto-generated tracking code per entry: `BLT-YYYY-NNNNNN`
  - Search by code/title/narrative/location/complainant; filter by entry type and status
  - Advance each entry through the status workflow (see Shared Workflows below)
  - Delete entries (soft delete); every action is audit-logged
- **Evacuation Centers** (`/evacuation-centers`): create/update centers
- **Evacuations** (`/evacuations`): run evacuation events, register evacuees time-in/time-out
- **Relief Inventory** (`/relief-inventory`): stock ledger with IN / OUT / ADJUSTMENT transactions
- **Relief Distribution** (`/relief-distributions`): record distribution events to beneficiaries

#### Programs Management (`/programs`)
- Manage government programs (4Ps, TUPAD, etc.): name, code, category, funding agency, budget, period, status
- Enroll beneficiaries, mark enrollments completed/dropped, remove beneficiaries
- Per-program beneficiary counts (active vs total)

#### Announcements Publishing (`/announcements/manage`)
- Compose announcements: title, content, type, priority
- **Save Draft** or **Publish Now**
- Edit any announcement
- Publish / re-publish drafts and archived items
- Archive published announcements (hidden from member board)
- Delete announcements
- All actions recorded in audit logs

#### Census & Reports (`/reports/census`)
- Build custom census reports: choose filters, groupings, metrics
- Save report definitions for reuse; edit/delete saved reports
- Print analytics reports as PDF (mPDF)

#### Document Types (`/request-types`)
- Add/edit/remove the catalog of requestable documents/services

---

### 🔑 Administrator

*Includes everything a Moderator can do, plus:*

#### Delete Rights (Admin-only destructive actions)
- Delete residents
- Delete households
- Delete service requests
- Delete complaints
- Delete calamities
- Delete evacuation centers

#### Users & Roles (`/users`)
- Change any user's role: admin / moderator / member

#### Barangay Profile & Settings (`/barangay`)
- Maintain barangay information (name, address, about, etc.)
- Manage barangay officials records (create/update/remove)

#### Audit Logs (`/audit-logs`)
- Full audit trail viewer: user, action, module, record, old/new values, IP address, user agent, timestamp

---

## 4. Shared Workflows Reference

### Service Request Status Flow
```
submitted → for_verification → approved → processing → ready_for_release → released
                                    ↘ rejected        ↘ cancelled
```

### Complaint Status Flow
```
submitted → under_review → verified → assigned → under_investigation → for_mediation
          → action_taken → resolved → closed   (↘ rejected)
```

### Announcement Lifecycle
```
draft ──publish──▶ published ──archive──▶ archived
   ▲                  │                      │
   └──────────── re-publish ◀─────────────────┘
```
Only **published** announcements appear on the member board. `published_at` drives each
member's unread badge count.

### Incident Blotter Status Flow
```
recorded → under_investigation → settled   → closed
                       ↘ referred ↗
```
Marking an entry **settled** or **closed** stamps it with a settlement timestamp;
optional remarks can be saved alongside any status change.

### Tracking Number Formats
| Record | Format |
|---|---|
| Service Request | `REQ-YYYY-NNNNNN` |
| Complaint | `CMP-YYYY-NNNNNN` |
| Calamity | `CAL-YYYY-NNNNNN` |
| Incident | `INC-YYYY-NNNNNN` |
| Incident Blotter | `BLT-YYYY-NNNNNN` |

---

## 5. Try It With Demo Accounts

Password for all demo accounts: **`password`**

| Do this | Log in as |
|---|---|
| File a request/complaint, read announcements | `pedro.mendoza@barangay.test` (Member) |
| Verify residents, publish announcements, manage programs | `maria@barangay.test` (Moderator) |
| Manage users/roles, view audit logs, delete records | `admin@barangay.test` (Admin) |