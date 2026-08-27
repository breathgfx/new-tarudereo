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

## Content management (Decap CMS)

All editable copy lives in [`content.json`](content.json), read at runtime by
[`js/content.js`](js/content.js) (see that file's header comment for how the
`data-content="..."` attributes on each page resolve). `admin/config.yml` and
`admin/index.html` expose that same file as a form-based editor at `/admin`,
so a non-technical editor can update copy without touching HTML.

This only needs to be set up **once**, and only by whoever manages the repo —
it's independent of wherever the public site ends up hosted (GitHub Pages now,
your own cPanel later, wherever): Decap's job is just committing changes to
`content.json` in this GitHub repo. Whatever serves the public site picks up
those changes on its own next deploy/sync.

**One-time setup** (needs a free [Netlify](https://netlify.com) account —
used only to host `/admin` and handle login, not the public site):

1. Netlify dashboard → **Add new site → Import an existing project** → connect
   GitHub → select this repo. Build command: leave blank. Publish directory: `/`.
2. **Site settings → Identity → Enable Identity.** Under registration, set
   "Invite only" (so random visitors can't self-register an editor account).
3. **Site settings → Identity → Services → Git Gateway → Enable.** This is
   what lets a logged-in Identity user save changes straight to this repo
   without needing their own GitHub account or token.
4. **Site settings → Identity → Invite users** → enter the client's email.
   They'll get an email to set a password, then land at `/admin`.
5. Visit `https://<your-netlify-site>.netlify.app/admin` yourself first to
   confirm it loads and you can log in.

**Editing:** the CMS shows one "All Site Content" document with collapsible
sections — one for site-wide fields (org name, contact info, etc., shared
across every page) and one per page. Saving commits directly to `main` and
the field tree covers every value currently in `content.json`.

**If you add a new field to `content.json` by hand later**, add the matching
entry in `admin/config.yml` too (same nesting) — otherwise the CMS editor
won't show it, and could silently drop it from the file on the next save,
since Decap rewrites the whole file from its own field definitions each time.

## Notes on originality

This template borrows the *information architecture and layout pattern* common
to nonprofit sites (which isn't copyrightable), not any specific organization's
text, images, logo, or exact styling. Once you drop in a new palette, logo, and
your own copy/photos per client, each resulting site is visually distinct.
