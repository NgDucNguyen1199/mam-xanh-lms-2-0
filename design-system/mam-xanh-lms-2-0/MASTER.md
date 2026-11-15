# Design System Master File — Mầm Xanh LMS 2.0

> **SOURCE OF TRUTH:** Generated and refined via `ui-ux-pro-max-skill` + **Argon 4.0** Architecture.
> When building or modifying any page in Mầm Xanh LMS 2.0, strictly adhere to the tokens and anti-slop rules below.

---

**Project:** Mầm Xanh LMS 2.0 (Academic AgTech Platform)
**Style:** Swiss Minimalist / Clean Academic SaaS (Linear / Coursera / Stripe / DLU LMS)
**Design Dials:** Variance `2/10` (Centered / Minimal) | Density `5/10` (Standard 8pt Grid)

---

## 1. Design Tokens (Color Palette)

| Role | Hex | CSS Variable | Usage |
|------|-----|--------------|-------|
| **Background (Off-White)** | `#F8FAFC` | `--mx-bg` | Page background (`body`, `#page`, `#page-wrapper`) |
| **Surface (Pure White)** | `#FFFFFF` | `--mx-surface` | Cards, blocks, navbar, tables, modals |
| **Muted Surface** | `#F1F5F9` | `--mx-surface-muted` | Table headers, inactive badges, hover states |
| **Primary (Deep Forest)** | `#1E3A2F` | `--mx-primary` | Primary buttons, active tab indicators, section left borders, today circle |
| **Primary Hover** | `#14532D` | `--mx-primary-hover` | Primary button hover/focus |
| **Secondary (Academic Green)** | `#166534` | `--mx-accent-green` | Links, completed badges, calendar event text |
| **Soft Highlight (Mint)** | `#DCFCE7` | `--mx-accent-soft` | Calendar event day circles (`#DCFCE7` bg + `#166534` text), Done pills |
| **Primary Text (Slate 900)** | `#0F172A` | `--mx-text-main` | Headings `h1-h3`, course titles, active navigation |
| **Body Text (Slate 800)** | `#1E293B` | `--mx-text-body` | Body copy, table cells, descriptions |
| **Muted Text (Slate 600)** | `#475569` | `--mx-text-muted` | Secondary nav links, table headers, dates |
| **Subtle Text (Slate 500)** | `#64748B` | `--mx-text-subtle` | Captions, timestamps, breadcrumbs |
| **Border (Hairline)** | `#E2E8F0` | `--mx-border` | All card, block, navbar, and table borders (`1px solid #E2E8F0`) |
| **Strong Border** | `#CBD5E1` | `--mx-border-strong` | Input borders, secondary button borders, card hover borders |

---

## 2. Typography & Vertical Rhythm (8pt Grid)

- **Font Stack:** `'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`
- **Scale:**
  - `h1 / .h1`: `1.65rem` (`26.4px`), `font-weight: 700`, `letter-spacing: -0.02em`, `line-height: 1.3`
  - `h2 / .h2`: `1.3rem` (`20.8px`), `font-weight: 600`, `letter-spacing: -0.015em`, `line-height: 1.35`
  - `h3 / .h3`: `1.1rem` (`17.6px`), `font-weight: 600`, `line-height: 1.4`
  - `h4 / .h4`: `0.98rem` (`15.7px`), `font-weight: 600`, `line-height: 1.45`
  - `Body / p / li`: `0.94rem` (`15px`), `line-height: 1.65`, reading measure constrained to `max-width: 75ch`

---

## 3. Core Layout Specifications

### 3.1. Dashboard 2-Column Layout (`/my/index.php`)
- **Container (`.path-my .main-inner`):** `max-width: 1280px`, centered with `24px` horizontal padding.
- **2-Column Wrapper (`#region-main-box.mx-dashboard-two-col` on Desktop `>= 992px`):**
  - `display: flex; flex-direction: row; align-items: flex-start; justify-content: space-between; gap: 20px;`
  - **Left Main Column (`#region-main`):** `flex: 1 1 calc(100% - 360px); width: calc(100% - 360px); max-width: calc(100% - 360px);` (Recently accessed courses + Course overview).
  - **Right Sidebar Column (`#region-bs-side-pre`):** `flex: 0 0 340px; width: 340px; min-width: 320px; max-width: 340px;` (Calendar `block_calendar_month` + Upcoming events `block_calendar_upcoming`).
- **Calendar Table (`table.calendarmonth`):**
  - Header `.calendar-controls .current`: Single centered horizontal line (`white-space: nowrap; font-weight: 600; font-size: 0.95rem`).
  - 7-column grid: `table-layout: fixed; width: 100%`, each `th`/`td` `14.28%` wide, centered.
  - Event days (`td.hasevent .day-number-circle`): `30x30px` circle, `#DCFCE7` background, `#166534` bold text, `1px solid #86EFAC`.

### 3.2. Course Outline & Evaluation Section (`/course/view.php`)
- **Section Headers (`.course-section-header`, `.mx-sec-header`):** `#F8FAFC` background, `border-left: 4px solid #1E3A2F`, `padding: 12px 18px`.
- **Activity Items (`.activity-item`):** `#FFFFFF` surface, `1px solid #E2E8F0`, `12px` radius, `38x38px` icon container with `24x24px` icon.
- **Inline Dates & Discussion Callout (`.activity-dates`, `.activity-description`):** `#F8FAFC` background, `border-left: 3px solid #1E3A2F`, `max-width: 75ch`.
- **Responsive 16:9 HD Video (`.mediaplugin_videojs`):** Full-width 16:9 container (`width="900" height="506"` attribute + `max-width: 100% !important` CSS override) with root-relative `/local/...` MP4 + WebM + Poster + Vietnamese WebVTT subtitles.

---

## 4. Anti-AI Slop & Pre-Delivery Checklist

- [x] **Zero Emojis:** Strictly 0 emojis in UI templates, PHP controllers, SCSS, or database records.
- [x] **No Double Card Wrappers:** Never nest bordered cards inside bordered cards without semantic purpose.
- [x] **No Fragile Flex Hacks:** Never apply percentage `max-width` to inner block regions (`aside[data-blockregion="side-pre"]`) that live inside fixed-width sidebars/drawers.
- [x] **WCAG AA/AAA Contrast:** Minimum `4.5:1` contrast on all text and interactive badges.
- [x] **Responsive Containment:** Zero horizontal overflow (`scrollWidth <= clientWidth`) at `375x812`, `1366x768`, and `1440x900`.
