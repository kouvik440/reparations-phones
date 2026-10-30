# Style Presets

Three presets. Pick one per dashboard. Never mix tokens between presets.

---

## dark-terminal

Electric cyan on near-black. For SaaS products, ops dashboards, developer tools.

```css
:root {
  /* Surfaces */
  --bg:        #080808;
  --surface:   #0f0f0f;
  --surface2:  #141414;
  --border:    #1e1e1e;
  --border2:   #2a2a2a;

  /* Text */
  --text:      #ededed;
  --muted:     #737373;
  --subtle:    #3a3a3a;

  /* Semantic */
  --accent:    #06b6d4;
  --accent-d:  rgba(6,182,212,0.1);
  --good:      #34d399;
  --bad:       #f87171;
  --warn:      #fbbf24;

  /* Typography */
  --sans:      'Geist', 'DM Sans', system-ui, sans-serif;
  --mono:      'Geist Mono', 'JetBrains Mono', monospace;

  /* Spacing */
  --s1: 4px;  --s2: 8px;   --s3: 12px;  --s4: 16px;
  --s5: 20px; --s6: 24px;  --s8: 32px;  --s10: 40px;

  /* Radii */
  --r-sm: 3px;
  --r:    6px;
  --r-lg: 8px;
}
```

**Categorical chart colours (colour-blind safe):**
```
Series 1: #06b6d4   ← accent / primary
Series 2: #a78bfa   ← violet
Series 3: #34d399   ← green
Series 4: #f59e0b   ← amber
```

**Font links:**
```html
<link href="https://fonts.bunny.net/css?family=geist:300,400,500,600&family=geist-mono:400,500&display=swap" rel="stylesheet">
```

---

## carbon-light

IBM Carbon Design System. Warm white background, IBM blue accent. For enterprise, B2B, formal reports.

```css
:root {
  /* Surfaces */
  --bg:        #f4f4f4;
  --surface:   #ffffff;
  --surface2:  #e8e8e8;
  --border:    #e0e0e0;
  --border2:   #c6c6c6;

  /* Text */
  --text:      #161616;
  --muted:     #525252;
  --subtle:    #8d8d8d;

  /* Semantic */
  --accent:    #0f62fe;
  --accent-d:  rgba(15,98,254,0.08);
  --good:      #198038;
  --bad:       #da1e28;
  --warn:      #f1c21b;

  /* Typography */
  --sans:      'IBM Plex Sans', system-ui, sans-serif;
  --mono:      'IBM Plex Mono', monospace;

  /* Spacing — same grid */
  --s1: 4px;  --s2: 8px;   --s3: 12px;  --s4: 16px;
  --s5: 20px; --s6: 24px;  --s8: 32px;  --s10: 40px;

  /* Radii — Carbon is squarer */
  --r-sm: 0px;
  --r:    2px;
  --r-lg: 4px;
}
```

**Categorical chart colours (IBM Carbon dataviz palette):**
```
Series 1: #0f62fe   ← IBM blue
Series 2: #9f1853   ← magenta
Series 3: #198038   ← green
Series 4: #fa4d56   ← red (use for negative only unless 4th series)
```

**Font links:**
```html
<link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600&family=ibm-plex-mono:400,500&display=swap" rel="stylesheet">
```

---

## neutral-bw

Pure black and white, Tailwind gray scale. No colour — any brand can be applied on top. For law firms, finance, conservative clients.

```css
:root {
  /* Surfaces */
  --bg:        #ffffff;
  --surface:   #fafafa;
  --surface2:  #f3f4f6;
  --border:    #e5e7eb;
  --border2:   #d1d5db;

  /* Text */
  --text:      #111827;
  --muted:     #6b7280;
  --subtle:    #9ca3af;

  /* Semantic — no colour, structure only */
  --accent:    #111827;
  --accent-d:  rgba(17,24,39,0.06);
  --good:      #111827;    /* use ↑ arrow, not colour */
  --bad:       #111827;    /* use ↓ arrow, not colour */
  --warn:      #6b7280;

  /* Typography */
  --sans:      'Geist', system-ui, sans-serif;
  --mono:      'Geist Mono', monospace;

  /* Spacing — same grid */
  --s1: 4px;  --s2: 8px;   --s3: 12px;  --s4: 16px;
  --s5: 20px; --s6: 24px;  --s8: 32px;  --s10: 40px;

  /* Radii */
  --r-sm: 2px;
  --r:    6px;
  --r-lg: 8px;
}
```

**Categorical chart colours (grayscale, pattern-differentiated):**
```
Series 1: #111827   ← near-black (solid line)
Series 2: #6b7280   ← mid gray (dashed line)
Series 3: #d1d5db   ← light gray (dotted line)
Series 4: #374151   ← dark gray (solid, thinner)
```

**Important for neutral-bw:** Since colour carries no semantic meaning, motion and border must carry it instead. Hover states must change both `background` AND `border-color`. See `motion.md`.

**Font links:**
```html
<link href="https://fonts.bunny.net/css?family=geist:300,400,500,600&family=geist-mono:400,500&display=swap" rel="stylesheet">
```

---

## Shared typography scale

Regardless of preset:

| Role | Size | Weight | Font | Used for |
|---|---|---|---|---|
| Display | 32–40px | 300 | mono | Primary KPI value |
| Heading | 16–18px | 600 | sans | Card titles, section headers |
| Body | 13–14px | 400 | sans | Descriptions, table content |
| Label | 10–11px | 500 | mono uppercase | KPI labels, column headers |
| Data | 12–13px | 400 | mono | Table numbers, axis labels |
| Caption | 10–11px | 400 | sans | Timestamps, helper text |

**Letter-spacing for labels:** `0.1em` on uppercase labels only. Never on body or values.
**Line-height:** 1.5 for labels/tables, 1.65 for body, 1.8 for prose.
