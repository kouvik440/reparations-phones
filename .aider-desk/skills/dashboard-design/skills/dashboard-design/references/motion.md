# Motion System

Motion makes dashboards feel responsive and premium. Every interactive element should give feedback. This is especially critical for `neutral-bw` where colour doesn't carry interactivity signals.

## Easing tokens

```css
:root {
  --ease-out:   cubic-bezier(0.16, 1, 0.3, 1);   /* most UI transitions */
  --ease-in:    cubic-bezier(0.4, 0, 1, 1);       /* exits */
  --ease-io:    cubic-bezier(0.4, 0, 0.2, 1);     /* in-out, spring feel */
  --ease-snap:  cubic-bezier(0.2, 0, 0, 1);       /* icon swaps, quick */
}
```

## Duration tokens

```css
:root {
  --dur-fast:   120ms;   /* hover states, button press */
  --dur-mid:    200ms;   /* filter transitions, tab switches */
  --dur-slow:   350ms;   /* page entrance, chart updates */
  --dur-count:  800ms;   /* KPI counter animation */
}
```

## Hover states

Every interactive element needs a visible hover state. In `neutral-bw`, also shift `border-color`.

```css
/* Button */
.btn {
  transition: color var(--dur-fast) var(--ease-out),
              background var(--dur-fast) var(--ease-out),
              border-color var(--dur-fast) var(--ease-out),
              transform var(--dur-fast) var(--ease-out);
}
.btn:hover { transform: translateY(-1px); }
.btn:active { transform: translateY(0) scale(0.98); }

/* Card */
.kpi-card {
  transition: border-color var(--dur-fast) var(--ease-out),
              box-shadow var(--dur-fast) var(--ease-out);
}
/* dark-terminal / neutral-bw hover */
.kpi-card:hover { border-color: var(--border2); }

/* Filter pill */
.filter-pill {
  transition: all var(--dur-fast) var(--ease-out);
}
.filter-pill.active { border-color: var(--accent); background: var(--accent-d); color: var(--accent); }

/* Table row */
tbody tr { transition: background var(--dur-fast); }

/* neutral-bw specific — border must also shift */
[data-preset="neutral-bw"] .btn:hover  { border-color: var(--text); }
[data-preset="neutral-bw"] .kpi-card:hover { border-color: var(--text); }
```

## Button press animation

```css
.btn, .page-btn, .filter-pill {
  /* Scale on press — always 0.97, never below 0.95 */
}
.btn:active         { transform: scale(0.97); }
.page-btn:active    { transform: scale(0.97); }
.filter-pill:active { transform: scale(0.97); }
```

## Page entrance — staggered rows

```css
@keyframes slideUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

.kpi-row      { animation: slideUp var(--dur-slow) var(--ease-out) 60ms  both; }
.charts-row   { animation: slideUp var(--dur-slow) var(--ease-out) 120ms both; }
.table-wrap   { animation: slideUp var(--dur-slow) var(--ease-out) 180ms both; }
```

## KPI counter animation

Animate KPI values up from 0 on load and on every filter change.

```js
function animateCounter(elId, target, duration = 800) {
  const el = document.getElementById(elId);
  const start = performance.now();
  const isCurrency = String(target).includes('$') || typeof target === 'number';
  const num = typeof target === 'string' ? parseFloat(target.replace(/[^0-9.]/g, '')) : target;

  function step(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    // Ease out cubic
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.round(num * eased);
    el.textContent = isCurrency ? fmt(current) : fmtN(current);
    if (progress < 1) requestAnimationFrame(step);
    else el.textContent = isCurrency ? fmt(num) : fmtN(num);
  }
  requestAnimationFrame(step);
}
```

In React:

```jsx
function useCountUp(target, duration = 800) {
  const [value, setValue] = useState(0);
  useEffect(() => {
    let start;
    const step = (now) => {
      if (!start) start = now;
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      setValue(Math.round(target * eased));
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }, [target]);
  return value;
}
// Usage: const animatedRevenue = useCountUp(kpis.total);
```

## Skeleton loading

Show skeletons during initial load and after filter changes when data takes >200ms.

```css
@keyframes shimmer {
  0%   { opacity: 0.4; }
  50%  { opacity: 0.7; }
  100% { opacity: 0.4; }
}

.skeleton {
  background: var(--surface2);
  border-radius: 3px;
  animation: shimmer 1.5s ease-in-out infinite;
}
```

KPI card skeleton — mirrors live layout exactly:

```html
<div class="kpi-card">
  <div class="skeleton" style="width:40%;height:8px;margin-bottom:12px;"></div>  <!-- label -->
  <div class="skeleton" style="width:65%;height:24px;margin-bottom:10px;"></div> <!-- value -->
  <div class="skeleton" style="width:50%;height:8px;margin-bottom:16px;"></div>  <!-- delta -->
  <div class="skeleton" style="width:100%;height:28px;border-radius:1px;"></div> <!-- sparkline -->
</div>
```

Table skeleton — 5 rows minimum:

```html
<!-- Header stays real — only rows are skeletons -->
<tbody id="table-body">
  <tr><td colspan="5"><div style="padding:10px 20px;"><div class="skeleton" style="height:12px;width:75%;"></div></div></td></tr>
  <tr><td colspan="5"><div style="padding:10px 20px;"><div class="skeleton" style="height:12px;width:60%;"></div></div></td></tr>
  <tr><td colspan="5"><div style="padding:10px 20px;"><div class="skeleton" style="height:12px;width:80%;"></div></div></td></tr>
  <tr><td colspan="5"><div style="padding:10px 20px;"><div class="skeleton" style="height:12px;width:55%;"></div></div></td></tr>
  <tr><td colspan="5"><div style="padding:10px 20px;"><div class="skeleton" style="height:12px;width:70%;"></div></div></td></tr>
</tbody>
```

## Chart transition

When filters update, never destroy and recreate charts. Use smooth in-place transitions:

```js
// HTML / Chart.js
chart.data.datasets[0].data = newData;
chart.update('active'); // smooth, 400ms

// React / Recharts — data prop update triggers automatic transition
// No extra code needed — Recharts animates by default
// Ensure isAnimationActive={true} (default) is not disabled
```
