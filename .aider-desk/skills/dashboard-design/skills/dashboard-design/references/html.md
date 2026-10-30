# HTML Dashboard Reference

Use this file when building an HTML dashboard. Copy the shell, fill in the data, wire up the interactions.

## Shell structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>[Dashboard Name]</title>
  <!-- Font from styles.md for chosen preset -->
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    /* Paste CSS tokens from styles.md for chosen preset */
    /* Then the layout CSS below */
  </style>
</head>
<body>
<div class="shell">
  <header class="dash-header"><!-- title, date range, export --></header>
  <div class="filter-bar"><!-- filters --></div>
  <main class="content">
    <div class="kpi-row"><!-- 3-4 KPI cards --></div>
    <div class="charts-row"><!-- primary chart + secondary --></div>
    <div class="table-wrap"><!-- sortable paginated table --></div>
  </main>
</div>
<script>
  // 1. DATA — define all datasets here, never inside render functions
  // 2. STATE — { period, region, page, sort, dir }
  // 3. update() — single function, called on every filter change
  // 4. renderKPIs(), renderCharts(), renderTable() — called by update()
  // 5. init() — called once on DOMContentLoaded
</script>
</body>
</html>
```

## Layout CSS

```css
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: var(--sans); background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }

.shell { display: flex; flex-direction: column; min-height: 100vh; }

/* Header */
.dash-header {
  position: sticky; top: 0; z-index: 20;
  background: var(--surface); border-bottom: 1px solid var(--border);
  height: 52px; display: flex; align-items: center;
  padding: 0 var(--s8); gap: var(--s4);
}
.dash-title { font-size: 15px; font-weight: 600; letter-spacing: -0.01em; }
.dash-header-right { margin-left: auto; display: flex; gap: var(--s3); }

/* Filter bar */
.filter-bar {
  background: var(--surface); border-bottom: 1px solid var(--border);
  padding: var(--s3) var(--s8); display: flex; gap: var(--s3);
  align-items: center; flex-wrap: wrap;
}
.filter-select {
  height: 32px; padding: 0 var(--s3);
  background: var(--surface2); border: 1px solid var(--border2);
  border-radius: var(--r); font-family: var(--mono); font-size: 12px;
  color: var(--text); cursor: pointer;
  transition: border-color 0.15s;
}
.filter-select:hover { border-color: var(--accent); }

/* Content */
.content { padding: var(--s8); display: flex; flex-direction: column; gap: var(--s4); }

/* KPI row */
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--s3); }

/* KPI card */
.kpi-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r-lg); padding: var(--s5);
  transition: border-color 0.15s;
}
.kpi-card:hover { border-color: var(--border2); }
.kpi-label {
  font-family: var(--mono); font-size: 10px; font-weight: 500;
  text-transform: uppercase; letter-spacing: 0.1em;
  color: var(--muted); margin-bottom: var(--s3);
}
.kpi-value {
  font-family: var(--mono); font-size: 36px; font-weight: 300;
  letter-spacing: -0.03em; line-height: 1; color: var(--text);
  margin-bottom: var(--s2); font-variant-numeric: tabular-nums;
}
.kpi-delta {
  font-family: var(--mono); font-size: 11px;
  margin-bottom: var(--s4); display: flex; align-items: center; gap: 4px;
}
.kpi-delta.up { color: var(--good); }
.kpi-delta.dn { color: var(--bad); }
.sparkline { height: 28px; display: flex; align-items: flex-end; gap: 2px; }
.spark-b { flex: 1; border-radius: 1px 1px 0 0; }

/* Charts row */
.charts-row { display: grid; grid-template-columns: 2fr 1fr; gap: var(--s3); }
.chart-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r-lg); padding: var(--s5);
}
.card-title { font-size: 14px; font-weight: 600; letter-spacing: -0.01em; margin-bottom: var(--s2); }
.card-sub { font-size: 12px; color: var(--muted); margin-bottom: var(--s5); }
.chart-container { position: relative; height: 220px; }

/* Table */
.table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); overflow: hidden; }
.table-header { display: flex; justify-content: space-between; align-items: center; padding: var(--s4) var(--s5); border-bottom: 1px solid var(--border); }
table { width: 100%; border-collapse: collapse; }
thead th {
  font-family: var(--mono); font-size: 10px; font-weight: 500;
  text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted);
  padding: var(--s2) var(--s5); text-align: left;
  border-bottom: 1px solid var(--border);
  cursor: pointer; user-select: none; white-space: nowrap;
}
thead th.sorted { color: var(--accent); }
thead th.num { text-align: right; }
tbody tr { transition: background 0.1s; }
tbody tr:hover { background: var(--surface2); }
tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border); }
tbody td { padding: var(--s2) var(--s5); font-size: 13px; vertical-align: middle; }
.col-primary { font-weight: 500; }
.col-num { font-family: var(--mono); font-size: 12px; text-align: right; font-variant-numeric: tabular-nums; }
.col-muted { color: var(--muted); font-size: 12px; }

/* Status badge — dot + text, never background fill in tables */
.status { display: inline-flex; align-items: center; gap: 5px; font-family: var(--mono); font-size: 11px; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; }

/* Empty state */
.empty-state {
  display: none; text-align: center; padding: var(--s10) var(--s8);
  color: var(--muted); font-size: 13px;
}
.empty-state.visible { display: block; }

/* Pagination */
.pagination { display: flex; align-items: center; gap: var(--s2); padding: var(--s3) var(--s5); border-top: 1px solid var(--border); }
.page-btn {
  height: 28px; min-width: 28px; padding: 0 var(--s2);
  background: none; border: 1px solid var(--border);
  border-radius: var(--r-sm); font-family: var(--mono); font-size: 11px;
  color: var(--muted); cursor: pointer; transition: all 0.12s;
}
.page-btn:hover:not(:disabled) { color: var(--text); border-color: var(--accent); }
.page-btn.active { border-color: var(--accent); color: var(--accent); background: var(--accent-d); }
.page-btn:disabled { opacity: 0.3; cursor: default; }
.page-info { font-family: var(--mono); font-size: 11px; color: var(--muted); margin-left: auto; }

/* Buttons */
.btn {
  height: 32px; padding: 0 var(--s4);
  background: none; border: 1px solid var(--border2);
  border-radius: var(--r); font-family: var(--mono); font-size: 11px;
  color: var(--muted); cursor: pointer; transition: all 0.12s;
  display: inline-flex; align-items: center; gap: var(--s2);
}
.btn:hover { color: var(--text); border-color: var(--accent); }
.btn-primary {
  background: var(--accent); border-color: var(--accent);
  color: var(--bg); font-weight: 500;
}
.btn-primary:hover { opacity: 0.9; }

/* Responsive */
@media (max-width: 768px) {
  .kpi-row { grid-template-columns: 1fr 1fr; }
  .charts-row { grid-template-columns: 1fr; }
  .content { padding: var(--s4); }
  .table-wrap { overflow-x: auto; }
  table { min-width: 640px; }
}
```

## JavaScript architecture

```js
// ── DATA ─────────────────────────────────────────────────────────────────────
// Define all data here. Never generate random values during render.
const RAW = [
  { date: '2026-01', region: 'North', channel: 'Direct', revenue: 12400, orders: 84, customers: 61 },
  // ... more rows
];

// ── STATE ─────────────────────────────────────────────────────────────────────
const state = { period: 'all', region: 'all', page: 1, sort: 'date', dir: 'desc', rowsPerPage: 10 };

// ── FILTER ────────────────────────────────────────────────────────────────────
function getFiltered() {
  return RAW.filter(r => {
    if (state.region !== 'all' && r.region !== state.region) return false;
    if (state.period !== 'all') { /* date filtering */ }
    return true;
  });
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
function renderKPIs(data) {
  const total = data.reduce((s, r) => s + r.revenue, 0);
  // Calculate delta from prior period data — never hardcode
  const prior = getPriorPeriod();
  const priorTotal = prior.reduce((s, r) => s + r.revenue, 0);
  const delta = total - priorTotal;
  const deltaPct = priorTotal > 0 ? (delta / priorTotal) * 100 : 0;
  const sign = delta >= 0 ? '+' : '';

  document.getElementById('kpi-revenue-val').textContent = fmt(total);
  document.getElementById('kpi-revenue-delta').textContent =
    `${sign}${fmt(delta)} (${sign}${deltaPct.toFixed(1)}%)`;
  document.getElementById('kpi-revenue-delta').className =
    `kpi-delta ${delta >= 0 ? 'up' : 'dn'}`;

  animateCounter('kpi-revenue-val', total); // from motion.md
}

// ── CHARTS ────────────────────────────────────────────────────────────────────
let lineChart, barChart; // always update, never destroy

function initCharts() {
  const ctx = document.getElementById('line-chart');
  lineChart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [{ label: 'Revenue', data: [], /* styles */ }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { /* custom */ } },
      scales: {
        x: { grid: { color: 'var(--border)' }, ticks: { color: 'var(--muted)', font: { family: 'var(--mono)', size: 10 } } },
        y: { grid: { color: 'var(--border)' }, ticks: { color: 'var(--muted)', font: { family: 'var(--mono)', size: 10 } },
             beginAtZero: false } // line chart: can truncate
      },
      animation: { duration: 400, easing: 'easeOutQuart' }
    }
  });
}

function renderCharts(data) {
  // Group data by month
  const byMonth = groupBy(data, 'month');
  lineChart.data.labels = Object.keys(byMonth);
  lineChart.data.datasets[0].data = Object.values(byMonth).map(rows => sum(rows, 'revenue'));
  lineChart.update('active'); // NOT destroy + recreate
}

// ── TABLE ─────────────────────────────────────────────────────────────────────
function renderTable(data) {
  // Sort
  const sorted = [...data].sort((a, b) => {
    const v = a[state.sort] < b[state.sort] ? -1 : 1;
    return state.dir === 'asc' ? v : -v;
  });

  // Paginate
  const total = sorted.length;
  const start = (state.page - 1) * state.rowsPerPage;
  const page = sorted.slice(start, start + state.rowsPerPage);

  // Render rows
  const tbody = document.querySelector('#main-table tbody');
  if (page.length === 0) {
    tbody.innerHTML = '';
    document.getElementById('empty-state').classList.add('visible');
    return;
  }
  document.getElementById('empty-state').classList.remove('visible');
  tbody.innerHTML = page.map(r => `
    <tr>
      <td class="col-primary">${r.name}</td>
      <td><span class="status"><span class="status-dot" style="background:${statusColor(r.status)}"></span>${r.status}</span></td>
      <td class="col-num">${fmt(r.revenue)}</td>
      <td class="col-num ${r.delta >= 0 ? 'up' : 'dn'}">${r.delta >= 0 ? '↑' : '↓'} ${Math.abs(r.deltaPct).toFixed(1)}%</td>
    </tr>
  `).join('');

  renderPagination(total);
}

function renderPagination(total) {
  const pages = Math.ceil(total / state.rowsPerPage);
  // render page buttons — wire each to: state.page = n; update();
}

// ── SORT ──────────────────────────────────────────────────────────────────────
document.querySelectorAll('thead th[data-col]').forEach(th => {
  th.addEventListener('click', () => {
    const col = th.dataset.col;
    if (state.sort === col) state.dir = state.dir === 'asc' ? 'desc' : 'asc';
    else { state.sort = col; state.dir = 'desc'; }
    state.page = 1;
    update();
    // Update sort indicators
    document.querySelectorAll('thead th').forEach(t => t.classList.remove('sorted'));
    th.classList.add('sorted');
    th.textContent = th.dataset.label + (state.dir === 'asc' ? ' ↑' : ' ↓');
  });
});

// ── CSV EXPORT ────────────────────────────────────────────────────────────────
function exportCSV() {
  const data = getFiltered(); // export FILTERED data, not raw
  const headers = ['Name', 'Status', 'Revenue', 'Orders'];
  const rows = data.map(r => [r.name, r.status, r.revenue, r.orders]);
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
  a.download = `export-${new Date().toISOString().slice(0,10)}.csv`; a.click();
}

// ── UPDATE — single function, all filters call this ───────────────────────────
function update() {
  const data = getFiltered();
  renderKPIs(data);
  renderCharts(data);
  renderTable(data);
}

// ── INIT ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initCharts();
  update();

  // Wire filters
  document.getElementById('region-select').addEventListener('change', e => {
    state.region = e.target.value; state.page = 1; update();
  });
});
```

## Sparkline helper

```js
function sparkline(container, values, colour) {
  const max = Math.max(...values);
  container.innerHTML = values.map(v => `
    <div class="spark-b" style="height:${(v/max*100).toFixed(0)}%;background:${colour};opacity:${v===max?0.8:0.3};"></div>
  `).join('');
}
// Usage: sparkline(document.querySelector('.sparkline'), [40,55,45,70,60,80,65,90,100], 'var(--good)');
```
