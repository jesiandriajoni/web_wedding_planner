# Product Requirement Document (PRD): Wedding Planner Application

> **Status: As-Built.** Dokumen ini disinkronkan dengan fitur yang benar-benar ada di kode
> (per 2026-08-07). Bagian yang berbeda dari rencana awal ditandai di catatan.

## Problem Statement
Calon pengantin dan kru Wedding Organizer (WO) sering kesulitan koordinasi progres kesiapan pernikahan, pembagian tugas, pencatatan alokasi dana, komparasi vendor, manajemen tamu & RSVP, jadwal rundown hari-H, status seserahan, dan pembuatan undangan online secara real-time dan terpusat.

## Solution
Aplikasi web kolaboratif Wedding Planner berbasis multi-tenant sederhana menggunakan Laravel 13, Vue 3, Inertia.js, dan Tailwind CSS. Akun pengantin dibuat oleh **admin** saat membuat project pernikahan; pengantin lalu login untuk mengisi modul-modul persiapan dari satu dasbor pusat, termasuk generate undangan online yang bisa dibagikan ke tamu.

## Roles
- **Global role** (kolom `users.role`): `admin` atau `pengantin`. Admin membuat project + akun pengantin, dan melihat daftar semua project. Admin tidak membuka menu isian project.
- **Project role** (pivot `project_user.role`): `wo`, `pengantin`, `keluarga`. Menentukan keanggotaan & akses ke menu project.

## User Stories

1. As an **Admin**, I want to create a new wedding project and generate the pengantin's login credentials (email + password) in the same form, so that the couple can immediately log in and start filling their data.
2. As an **Admin**, I want to see a list of all projects and a read-only list of pengantin accounts, so that I can manage onboarding.
3. As a **Pengantin**, I want to log in with the email/password given by admin (email is used as the username) and change my password anytime from the Profile page, so that my account is secure.
4. As a **Pengantin**, I want to see a project dashboard with a checklist progress percentage and spending summary, so that I can assess overall preparation status.
5. As a **Pengantin / WO**, I want to define a total budget with percentage-based default allocations (Vendor 50%, Katering 30%, Seserahan 10%, Lainnya 10%), so that I can estimate costs.
6. As a **Pengantin / WO**, I want to log multiple payments per vendor, so that each vendor's `paid_amount` and status (`pending`, `dp`, `paid`) update automatically.
7. As a **WO / Pengantin**, I want to manage vendor contacts, prices, and upload contract MoU files, and compare package prices of similar vendors, so that vendor info is centralized.
8. As a **WO / Pengantin**, I want to manage guest lists categorized by side (`pria`, `wanita`, `bersama`), so that I can organize invitations.
9. As a **Guest**, I want to open a published invitation URL without logging in, view the prewedding gallery, music, akad & resepsi details, submit RSVP, and write a guest-book message, so that I can confirm attendance easily.
10. As a **Pengantin**, I want to fill invitation content (prewed photos, music link, akad & resepsi location/time/maps) and click **Generate** to publish it, producing a shareable link `/undangan/{slug}` for guests. Before publishing, the public link returns 404.
11. As a **WO**, I want to create a chronological rundown for the wedding day and export it to PDF from the server, so that I can print/share it.
12. As a **Pengantin**, I want to track seserahan items on a **drag-and-drop Kanban board** across 4 statuses (`pending`, `purchased`, `delivered`, `returned`) and attach an external courier tracking URL, so that I can monitor logistics.
13. As a **Pengantin**, I want a static onboarding guide, so that I can learn how to use the app.

## Implementation Decisions

### 1. Technology Stack & Packages
- **Framework**: Laravel 13.x (PHP 8.3) & Vue 3 + Inertia.js.
- **Auth**: Laravel Breeze (Inertia + Vue). Login identifier = **email** (labeled "Username" in UI). Self-registration (`/register`) stays enabled alongside admin account creation. Root `/` redirects to `/login`; unauthenticated access to protected routes redirects to `/login` (`redirectGuestsTo`).
- **Styling**: Tailwind CSS **v4** (`@import "tailwindcss"` in `app.css`). Dark mode is **disabled** via `@custom-variant dark (&:where(.dark, .dark *))` (no `.dark` class is ever applied). Palette: cream `primary-light` (#E9DCCD) + Sage + Forest Green scales.
- **Database**: MySQL (`DB_CONNECTION=mysql`).
- **PDF Generation**: `barryvdh/laravel-dompdf`.  
- **File storage**: `storage/app/public` via `php artisan storage:link` (vendor MoU in `contracts/`, invitation photos in `invitations/{project_id}/`).

### 2. Modules and Schemas
- **Users**: adds `role` (`admin` | `pengantin`, default `pengantin`).
- **Projects**: `name`, `slug` (unique, for `/undangan/{slug}`), `wedding_date`, `total_budget`. **Invitation fields**: `is_published` (bool), `prewed_photos` (JSON array of storage paths), `music_url`, `akad_location`, `akad_datetime`, `akad_maps_url`, `resepsi_location`, `resepsi_datetime`, `resepsi_maps_url`. Admin-only creation; the new pengantin account is attached as pivot role `pengantin`.
- **ProjectUser (pivot)**: `role` = `wo` | `pengantin` | `keluarga`.
- **Checklists**: `project_id`, `status` (`pending` | `done`), `assigned_to` (text). *Read-only in-app*: the dashboard aggregates progress, but there is no checklist CRUD UI yet (data seeded). **[gap vs original plan]**
- **Guests**: `name`, `side` (`pria`/`wanita`/`bersama`), `rsvp` (`pending`/`hadir`/`absen`), `pax` (default 2), `guest_book_message`.
- **Vendors**: `name`, `category`, `contact`, `package_price`, `paid_amount`, `status` (`pending`/`dp`/`paid`), `mou_path` (uploaded to `storage/app/public/contracts`).
- **Payments**: **separate table** `payments` (`project_id`, `vendor_id`, `amount`, `notes`). A saved/deleted Payment recomputes the parent vendor's `paid_amount` + status via model events. **[refined vs original inline-field plan]**
- **Rundowns**: chronologically ordered by `time`; exportable to PDF.
- **SeserahanItems**: `item_name`, `status` ENUM `pending`/`purchased`/`delivered`/`returned`, `tracking_url`, `price`. Managed via drag-and-drop Kanban. **[statuses differ from original `planning/shipping/purchased/ready`]**

## Testing Decisions
- **Approach**: integration tests over external behavior.
- **Automated Tests**:
  - Registration, login, and admin project-creation (which also creates the pengantin account).
  - Guest RSVP public submission updates the database.
  - Rundown PDF generation returns a valid PDF.
  - Budget allocation + payment tracking logic.
  - Invitation publish gating: unpublished `/undangan/{slug}` returns 404; published renders content.

## Out of Scope
- Automatic WhatsApp/email integrations.
- In-app chat/messaging (rundown assignee is static text).
- Real-time websockets (updates on page load/action).
- Checklist management UI, invitation music file upload (URL only), Spotify embeds, per-photo captions/reorder, forced password change on first login, invitation draft/unpublish toggle.
- Seserahan Kanban drag-and-drop uses native HTML5 DnD → **no touch/mobile drag support**.

## Further Notes
- Cream + Sage/Forest Green palette for a warm, elegant wedding theme. Focus on clean layout via Tailwind CSS.
