# Blackthorn Hunting

WordPress website for Blackthorn Hunting: guided deer stalking, and deer management for landowners, in Cambridgeshire, England. Built by Liam Rourke. This is a working draft.

## Run the site locally

Needs Node 20+ and nothing else. From the repo root (PowerShell on Windows, not Git Bash):

```
npx @wp-playground/cli@3.1.48 server --port=8881 "--mount=./theme/cambridgeshire-deer-stalking:/wordpress/wp-content/themes/cambridgeshire-deer-stalking" --blueprint=./dev/playground-blueprint.json
```

Then open http://localhost:8881. The port answers "WordPress is not ready yet" for a short while during boot. The theme self-assembles on first load: it creates the pages from its patterns (Home, Deer Stalking, The Deer, Deer Management, Contact, Privacy Policy), sets the front page, permalinks and site title. The blueprint sets British English.

Warning: this local sandbox wipes its database on every restart. Real copy changes belong in the pattern files at `theme/cambridgeshire-deer-stalking/patterns/`, not in wp-admin. On real hosting the setup runs once only, and after that all wording is edited in the WordPress editor.

## Live demo

https://trg-liam.github.io/deer_stalking/

Every push to `main` runs a GitHub Action that boots the same WordPress sandbox, snapshots all pages to static HTML and deploys them to GitHub Pages. The demo is visual only: the enquiry form does not send, and the demo is marked noindex so search engines leave it alone.

## Layout

- `theme/cambridgeshire-deer-stalking/` the WordPress block theme (the deliverable). The folder name is an internal slug and is not shown to visitors.
- `assets/images/` master images (full-resolution originals in `raw/`)
- `dev/` WordPress Playground blueprints for local dev and for the demo build

## Before going live on real hosting

- Fill every square-bracket placeholder (phone, email, and the sections waiting on the owner).
- Connect a real enquiry form in place of the preview form on the Contact page.
- Complete the privacy policy.
- Settings > General: confirm Site Language is English (UK).
- Settings > Reading: make sure "Discourage search engines" is off.
