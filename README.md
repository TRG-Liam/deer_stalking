# Cambridgeshire Deer Stalking (working name)

WordPress website for Mitch Baker's guided deer stalking business in Cambridgeshire, England. Built by Liam Rourke. The business name is a placeholder until Mitch picks one.

The project brief, hard rules and status log live in [CLAUDE.md](CLAUDE.md). Plan: [PLAN.md](PLAN.md). Research: [RESEARCH.md](RESEARCH.md).

## Run the site locally

Needs Node 18+ and nothing else. From the repo root (PowerShell on Windows, not Git Bash):

```
npx @wp-playground/cli@latest server --port=8881 "--mount=./theme/cambridgeshire-deer-stalking:/wordpress/wp-content/themes/cambridgeshire-deer-stalking" --blueprint=./.claude/playground-blueprint.json
```

Then open http://localhost:8881. The theme self-assembles on first load: it creates the five pages from its patterns, sets the front page, permalinks and site title.

Warning: this local sandbox wipes its database on every restart. Real copy changes belong in the pattern files at `theme/cambridgeshire-deer-stalking/patterns/`, not in wp-admin.

## Live demo

Every push to `main` runs a GitHub Action that boots the same WordPress sandbox, snapshots all pages to static HTML and deploys them to GitHub Pages. The demo is visual only: the enquiry form is a mock until the site moves to real hosting.

## Layout

- `theme/cambridgeshire-deer-stalking/` the WordPress block theme (the deliverable)
- `design-mock/` the approved Claude Design export (design source of truth)
- `assets/images/` master images (raw generations in `raw/`)
- `.claude/` local dev config (Playground blueprint, launch config)
