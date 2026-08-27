# TARUDEREO Site

Plain HTML/CSS/JS site for TARUDEREO (Tanzania Rural Development and Relief
Organization), a registered NGO in Kasulu, Kigoma Region. Content is
data-driven (see [`content.json`](content.json) below) so copy can be edited
without touching HTML.

## Files

```
index.html               Homepage — hero, Who We Are, focus areas teaser, CTA
about.html                Who We Are / Mission / Vision / Story
focus-areas.html          The 5 strategic focus areas (Environment, Health, Agriculture, Education, Micro-Economic Development)
programmes.html           How programmes are developed (currently a holding page — no named programmes yet)
impact.html               What TARUDEREO documents + its accountability commitment
why-kigoma.html           Why the org focuses its work in Kigoma Region
partnership.html          Partnership types + partnership inquiry form
transparency.html         Governance commitment + organisational documents (Org Profile, Annual Reports, Policies, Financial Info)
resources.html            Links to org info, reports, publications, community stories
community-stories.html    Placeholder for community story content
support.html              Donation page (one-time/monthly gift form)
contact.html              Address/email/phone/website + contact form
content.json              All editable copy — read at runtime by js/content.js
css/style.css             All design tokens + component styles
js/main.js                Mobile nav, cookie banner, back-to-top, form UX
js/content.js             Reads content.json and fills every data-content="..." element
admin/                    Custom PHP admin panel for editing content.json + logo/hero photo (see below)
```

## To customize

1. **Brand colors/type** — edit the `:root` variables at the top of `css/style.css`
   (`--ink`, `--paper`, `--teal`, `--ochre`, `--olive`, `--stone`) and the two
   Google Fonts in the `@import` line. Everything else inherits automatically.
2. **Logo / hero photo** — `img/logo.png` and `img/hero-maternal-health.jpg`.
   Swap either file directly (same filename) via `/admin`, or by hand — every
   page references them by that exact path.
3. **Copy** — edit [`content.json`](content.json) directly, or through `/admin`
   (see the section below). No HTML file needs touching for a text change.
4. **Adding a page** — there's no template-duplication step anymore (that was
   the old Locations/Program-Detail pattern, since removed): copy the closest
   existing page's structure, add a matching entry under `pages` in
   `content.json`, and add the nav link across all pages' `<nav class="nav-links">`
   and footer `<div class="footer-grid">`.
5. **Contact/partnership/donation forms** — `js/main.js` currently just shows
   a client-side confirmation message. Wire them to a real endpoint (Formspree,
   your own API, etc.) and a real payment processor for `support.html` before
   going live.

## Content management (custom PHP admin)

All editable copy lives in [`content.json`](content.json), read at runtime by
[`js/content.js`](js/content.js) (see that file's header comment for how the
`data-content="..."` attributes on each page resolve). `admin/` is a small,
purpose-built PHP app that edits that same file (plus the logo and homepage
hero photo) through a plain form at `/admin` — no third-party CMS service,
no git commits involved, just reads and writes files directly on the server.

**This needs a PHP-capable server — it will NOT work on GitHub Pages**
(GitHub Pages only serves static files; it can't execute `.php`). It works
today against XAMPP for local testing, and will work once the site is hosted
somewhere PHP runs (cPanel, etc.). Requires the `gd`, `session`, and `json`
PHP extensions, all enabled by default on basically every PHP host.

An earlier version of this used Decap CMS + DecapBridge (git-based, so it
worked on GitHub Pages) — dropped in favor of this because Decap's generic
media-library UI was needlessly complex for a site with only two swappable
images. If you ever need edits to show up on a *static* host again before
the cPanel move happens, that git-based approach is the fallback to revisit.

**One-time setup on a new server:**

1. Copy `admin/config.sample.php` to `admin/config.local.php` (same folder).
   It's gitignored — it holds this deployment's real password and must never
   be committed.
2. Generate a password hash and paste it into `config.local.php`:
   ```bash
   php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT), PHP_EOL;"
   ```
3. Make sure the web server user can write to `content.json` and the `img/`
   folder (on shared/cPanel hosting this is usually already true for files
   you uploaded yourself).
4. Visit `/admin`, log in, confirm the form loads with the current content.

**Editing:** one page — site-wide fields at the top (org name, contact info,
etc., shared across every page), then a collapsible section per page below.
Saving overwrites `content.json` on disk immediately. The two image fields
(logo, homepage hero photo) re-encode whatever you upload to match the
existing file's format and overwrite it in place, so no other file needs to
change. Adding photo slots for program/location cards (currently plain color
blocks) isn't wired up — ask for that once real photos exist for them.

**If you add a new field to `content.json` by hand**, the admin form picks it
up automatically next page load — `admin/index.php` renders whatever fields
`content.json` actually has, so there's no separate schema to keep in sync.

**Because this writes directly to the live server's disk, not git:** edits
made through `/admin` on a real deployment won't appear back in this GitHub
repo automatically. If you want the repo to stay the source of truth, pull
the live `content.json`/`img/` back down periodically, or treat the deployed
copy as authoritative once it's live and stop editing this repo's copies by
hand.

## Notes on originality

This template borrows the *information architecture and layout pattern* common
to nonprofit sites (which isn't copyrightable), not any specific organization's
text, images, logo, or exact styling. Once you drop in a new palette, logo, and
your own copy/photos per client, each resulting site is visually distinct.
