# NGO Site Template

A reusable, plain HTML/CSS/JS template for community-org / NGO-style websites.
Structure (nav, hero, stats, program & location card grids, reports list, contact
form, footer) is modeled on common nonprofit site patterns, but the visual design
(palette, type, logo mark, "root line" motif) is original — safe to use as a base
for multiple client sites.

## Files

```
index.html            Homepage
about.html             Mission / Vision / Values / Story
programs.html          Program listing (card grid)
program-detail.html    Single program page (duplicate per program)
locations.html         Location/branch listing (card grid)
location-detail.html   Single location page (duplicate per location)
impact.html            Stats + narrative + forward-looking goals
reports.html           Downloadable annual reports list
contact.html           Address/email/phone + contact form
css/style.css          All design tokens + component styles
js/main.js             Mobile nav, cookie banner, animated counters, form UX
```

## To customize for a new client site

1. **Brand colors/type** — edit the `:root` variables at the top of `css/style.css`
   (`--ink`, `--paper`, `--teal`, `--ochre`, `--olive`, `--stone`) and the two
   Google Fonts in the `@import` line. Everything else inherits automatically.
2. **Logo mark** — replace the inline `<svg class="logo-mark">` in each page's
   header with the client's own icon (keep it ~34×34).
3. **Copy** — every `[bracketed placeholder]` is a spot to fill in: org name,
   mission text, stats, program names, addresses, etc. Search for `[` to find them all.
4. **Photography** — the card/hero media blocks currently use flat gradient
   placeholders (`.card-media`, `.hero .roots`). Swap in real photos via
   `background-image` or an `<img>` inside `.card-media` once you have licensed
   images for that client — don't reuse another organization's photos.
5. **Duplicate detail pages** — copy `program-detail.html` /
   `location-detail.html` once per program/location and update the content and
   filename, then point the card `href`s at the new files.
6. **Contact form backend** — `js/main.js` currently just shows a "Message sent"
   confirmation client-side. Wire the form to a real endpoint (Formspree, your
   own API, etc.) before going live.
7. **Reports** — add one `.card` per year in `reports.html`, linking to the
   actual PDF file.

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
