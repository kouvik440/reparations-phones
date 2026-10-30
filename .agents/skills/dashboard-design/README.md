# dashboard-design-skill

Agent skill for building production-quality data dashboards in HTML or React. KPI cards with real deltas, 5 chart types, filter systems, sortable paginated tables, and CSV export — with three built-in style presets and a motion system.

Works with Claude Code, Cursor, Codex, and Windsurf.

---

## Install

**Via the skills CLI (recommended)**
```bash
npx skills add Judiedesigns/dashboard-design-skill
```

**Via npm**
```bash
npm install -g dashboard-design-skill
```
The skill installs itself automatically to your agent's skills directory on `postinstall`.

**Via GitHub directly**
```bash
npx skills add github:Judiedesigns/dashboard-design-skill
```

---

## What it does

Teaches an AI agent to build complete, working dashboards — not just pretty screenshots. Every output includes real calculated data, wired-up interactions, and correct component states.

- **Two output formats** — single HTML file (Chart.js) or React component (Recharts)
- **Three style presets** — `dark-terminal`, `carbon-light`, `neutral-bw`
- **Enforces correct KPI deltas** — calculated from real prior period data, never random
- **Wires up pagination, sorting, empty states, and CSV export** that actually works
- **Motion system** — easing tokens, counter animations, skeleton loaders, hover states
- **Charts update in place** on filter change — no destroy/recreate flicker

---

## Usage

Specify your output format and preset in the prompt:

```
Build a sales dashboard in HTML using the dark-terminal preset
with revenue, orders, and AOV KPIs.
```

```
Create a React analytics dashboard using carbon-light style
with filters for region and date range.
```

```
Build a neutral-bw HTML dashboard for a law firm —
no colour, clean black and white with smooth hover states.
```

---

## Style presets

| Preset | Accent | Background | Font | Best for |
|---|---|---|---|---|
| `dark-terminal` | `#06b6d4` cyan | `#080808` near-black | Geist Mono | SaaS, ops, developer tools |
| `carbon-light` | `#0f62fe` IBM blue | `#f4f4f4` warm white | IBM Plex Sans | Enterprise, B2B, formal reports |
| `neutral-bw` | `#111827` near-black | `#ffffff` white | Geist | Any brand, law, finance |

---

## Skill files

```
skills/dashboard-design/
├── SKILL.md                ← core rules, chart selection, layout order, mistakes table
└── references/
    ├── html.md             ← complete HTML template + Chart.js configs + JS architecture
    ├── react.md            ← React component structure + Recharts configs for all 5 chart types
    ├── styles.md           ← all token values for each preset (copy-paste ready)
    └── motion.md           ← easing tokens, hover states, counter animation, skeleton loading
```

---

## Non-negotiable rules

1. Layout order is always fixed: header → filters → KPIs → charts → table
2. All numbers use monospace font with `tabular-nums`
3. One `update()` function handles all filter changes simultaneously
4. Charts use `chart.update()` — never destroy and recreate
5. KPI value is `font-weight: 300` — lighter weight looks more premium at large sizes
6. Bar chart Y-axis always starts at zero
7. Donut charts have 5 segments maximum
8. KPI deltas show both absolute and relative: `+$3,681 (+8.2%)`

---

## Part of

This skill was created alongside the [Mars Dashboard Design Visual Guide](https://mars-portfolio.figma.site/) — a comprehensive reference for dashboard design covering visual hierarchy, KPI cards, chart selection, colour systems, typography, layout, and component states.

---

## License

MIT
