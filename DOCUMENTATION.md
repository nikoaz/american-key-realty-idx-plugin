# American Key Realty — Custom IDX Plugin Project

**Project Start Date:** March 11, 2026
**Site:** americankeyaz.com
**Client Contact:** Niko Mitchell (niko / nm@amazingoffer.com)

---

## Project Goal

Build a custom WordPress plugin that extends the existing FlexMLS IDX plugin by automatically generating a fully branded, Elementor-based landing page for each active MLS listing — keeping visitors on americankeyaz.com instead of redirecting them to my.flexmls.com.

---

## Current Environment — Confirmed

| Item | Detail |
|------|--------|
| WordPress Version | 6.9.1 |
| PHP | 8.4 |
| Server | WP Engine (managed hosting) |
| Theme | Hello Elementor v3.4.6 |
| Elementor | v3.35.6 (free) — Active |
| Elementor Pro | v3.35.1 — Active |
| FlexMLS IDX Plugin | v3.15.11 — Active, API configured |
| Contact Form 7 | v6.1.5 — Active |
| MC4WP Mailchimp | v4.12.0 — Installed, NOT connected |
| Wordfence | v8.1.4 — Active |
| WPCode Lite | v2.3.4 — Active |
| Duplicate Page | v4.5.6 — Active |
| SEO Plugin | None installed |
| Object Cache | None (Redis/Memcached not set up) |
| Multisite | No |
| FlexMLS OAuth Portal | Configured and working |
| IDX Permalink Base | idx |
| IDX Search Results Page | Listings (Page ID 970) |
| IDX Template Version | Version 2 |
| Open IDX Links Setting | Separate from WordPress (the core problem) |
| Active Listings | 6 listings (as of March 11, 2026) |

---

## Environment Notes — Staging

| Item | Detail |
|------|--------|
| Staging URL | americankeystg.wpenginepowered.com |
| Production URL | americankeyaz.com |
| Hosting | WP Engine (both environments) |

### wp-config.php Hardcoded URLs (Staging Only)

Due to an unresolved redirect issue that could not be traced to any common location (database, .htaccess, plugin settings, or WP Engine config), WP_HOME and WP_SITEURL are explicitly defined in wp-config.php on the staging environment only. This was done to prevent staging from redirecting to production.

This definition does NOT exist on production and should never be added there.

### Pre-Work Staging Verification Rule

Before beginning any development session on staging, verify that americankeystg.wpenginepowered.com is NOT redirecting to americankeyaz.com. If a redirect to production is detected, stop all work immediately and alert Niko. Production must never be touched.

---

## Current Site Pages

| Page | Slug | Builder |
|------|------|---------|
| Home | / | Elementor |
| Listings | /listings/ | Elementor |
| Our Team | /about/ | Elementor |
| Contact | /contact/ | Elementor |
| Why Us | /why-us/ | Elementor |
| Our Process | /our-process/ | Elementor |
| Careers | /careers/ | Elementor |
| Test Listing Details | (Draft) | Elementor |

---

## Existing Elementor Templates

| Template | Type | Scope |
|----------|------|-------|
| AK Header | Header | Entire site |
| Elementor Footer #1254 | Footer | Entire site |
| Default Kit | Global Kit | Site-wide styles |

---

## Branding

- Primary Color: Navy blue (#1a2a4a approx)
- Accent/CTA Color: Teal/Green
- Secondary Accent: Coral/salmon-red
- Logo: American Key Realty with patriotic star/flag icon
- Phone: 602-326-0851
- Address: 3030 N Central Ave #1101, Phoenix, AZ 85012
- Market: Phoenix metro — Gilbert, Queen Creek, Goodyear, Glendale, Sun City, Maricopa, Florence, Litchfield Park

---

## Property Types (from MLS)

Residential, Residential Lease, Land, Comm/Industry Sale, Comm/Industry Lease, Multiple Dwellings, Business Opportunity

---

## Team / Users

| Name | Username | Email |
|------|----------|-------|
| Craig Malton | craig | cm@amazingoffer.com |
| Hailey McLaughlin | hailey | hmclaughlin@amazingoffer.com |
| Josh Dingman | Josh | jdingman@amazingoffer.com |
| Makayla Hilliard | makayla | mhilliard@amazingoffer.com |
| Niko Mitchell | niko | nm@amazingoffer.com |
| Paul | paul | pa@amazingoffer.com |

---

## The Core Problem (Confirmed by Testing)

Clicking any listing on /listings/ redirects the user completely off-site to my.flexmls.com/elliottmcallister/ — the FBS-hosted portal. American Key branding, CTAs, phone number, and lead capture are all lost. No SEO value accrues to americankeyaz.com.

---

## Plugin Feature List (Prioritized)

### Phase 1 — Core (Must Have)
- Auto-create a branded WP page for each active listing
- Elementor template with dynamic listing data tokens
- Auto-update pages when listing data changes
- Auto-handle off-market listings (redirect or draft)
- SEO-friendly URLs (/listings/address-city-state-zip/)
- Contact Form 7 integration on each listing page
- Editable CTA buttons with configurable link destinations
- Admin dashboard: Listing Page Manager

### Phase 2 — Enhanced
- Multiple templates by property type
- Per-listing Rank Math SEO meta (title, description, OG tags)
- Social sharing Open Graph + Twitter Card auto-population
- Agent bio block (dynamic or static)
- Mortgage calculator widget
- Comparable/related listings section
- Photo gallery control (lightbox, carousel)

### Phase 3 — Advanced
- Mailchimp lead capture on listing pages
- Per-listing analytics (views, CTA clicks, form submissions)
- SMS/text listing info button
- Open house countdown/RSVP block
- Virtual tour / video embed block
- PDF/print listing sheet

---

## Outstanding Decisions (Needed From Client)

- Staging site — WP Engine — americankeystg.wpenginepowered.com (confirmed March 13, 2026)
- SEO plugin — Recommend Rank Math (free). Approve install?
- CTA buttons — What should "Request a Showing" / "Contact Agent" do? Form on page, phone call, Calendly, or Contact page link?
- Agent assignment — Show listing agent from MLS data, or generic American Key team block?
- Mailchimp — Connect it? API key needed from Mailchimp account.
- Number of templates to start — One universal, or separate for Residential vs. Commercial vs. Land?

---

## Project Timeline

| Phase | Description | Duration |
|-------|-------------|----------|
| 1 | Staging setup + pre-flight | 3-5 days |
| 2 | Plugin core development | 3-4 weeks |
| 3 | Elementor template build | 2 weeks |
| 4 | Lead capture + CTA integration | 1 week |
| 5 | SEO + social meta automation | 1 week |
| 6 | Testing and QA on staging | 1-2 weeks |
| 7 | Launch to live site | 3-5 days |
| 8 | Documentation and handoff | 3-5 days |
| Total | | ~9-12 weeks |

---

## Development Notes and Lessons Learned
## Plugin — Staging Environment

| Item | Detail |
|------|--------|
| Plugin File | american-key-idx.php |
| Current Version | v0.1.0 — Minimal scaffold |
| GitHub Repo | https://github.com/nikoaz/american-key-realty-idx-plugin |
| Staging Install Path | /wp-content/plugins/american-key-realty-idx-plugin/ |
| Staging WP Admin | https://americankeystg.wpenginepowered.com/wp-admin/ |
| Status | Staging restored from production — March 16, 2026 |

### Plugin Development Rules
- Deploy one feature at a time — confirm working before adding the next
- All code committed to GitHub first, then uploaded to staging
- Install via WP Admin > Plugins > Upload Plugin
- Disable by renaming folder to _american-key-realty-idx-plugin via FileZilla
- Never install on production until fully tested on staging

---
### Build Incrementally
The initial large scaffold approach caused a critical error that locked wp-admin on staging (required a production-to-staging restore via WP Engine). All plugin development must be deployed in small, testable increments — one feature at a time, verified working before the next step.

### WP Engine Dashboard
Claude should not interact with the WP Engine dashboard UI directly — navigation of push/pull and file manager was unreliable. Niko handles all WP Engine dashboard operations directly.

### Recovery Method
When wp-admin is inaccessible, the go-to recovery method is renaming the plugins folder to _plugins via SFTP. The underscore prefix is Niko's preferred convention for disabling folders (also sorts them to the top alphabetically).

### SFTP Access
- Client: FileZilla
- Correct remote root for staging: /sites/americankeystg/

---

## Reference Links

- FlexMLS IDX Plugin Docs: https://go.wearefbs.com/idx-help-center/flexmls-idx-wordpress-plugin
- FBS Support Email: idxsupport@flexmls.com | Phone: 888-525-4747 x.171
- Staging: https://americankeystg.wpenginepowered.com (confirmed March 13, 2026)
- FlexMLS IDX Settings: https://americankeyaz.com/wp-admin/admin.php?page=fmc_admin_settings
- Live Listings Page: https://americankeyaz.com/listings/
- GitHub Repo: https://github.com/nikoaz/american-key-realty-idx-plugin
