# React Dashboard Reference

Use this file when building a React dashboard. Recharts for charts, CSS variables for theming.

## Component structure

```jsx
// DashboardPage.jsx — single file, no sub-components unless specified
import { useState, useCallback, useMemo, useEffect, useRef } from 'react';
import {
  LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, Tooltip,
  ResponsiveContainer, XAxis, YAxis, CartesianGrid, AreaChart, Area
} from 'recharts';

// ── DATA ─────────────────────────────────────────────────────────────────────
// Define outside component — stable reference, no rerenders
const RAW_DATA = [
  { month: 'Aug', revenue: 31200, orders: 210, region: 'North' },
  { month: 'Sep', revenue: 34800, orders: 234, region: 'North' },
  // ... more rows
];

// ── HELPERS ──────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const fmtN = (n) => new Intl.NumberFormat('en-US').format(n);
const pct = (v, total) => total > 0 ? ((v / total) * 100).toFixed(1) + '%' : '—';

// ── COMPONENT ────────────────────────────────────────────────────────────────
export default function Dashboard() {
  const [region, setRegion] = useState('all');
  const [period, setPeriod] = useState('30d');
  const [sort, setSort] = useState({ col: 'revenue', dir: 'desc' });
  const [page, setPage] = useState(1);
  const PAGE_SIZE = 10;

  // Single filtered dataset — all rendering derives from this
  const data = useMemo(() => {
    return RAW_DATA.filter(r => region === 'all' || r.region === region);
  }, [region, period]);

  // KPI calculations — always from data, never hardcoded
  const kpis = useMemo(() => {
    const total = data.reduce((s, r) => s + r.revenue, 0);
    // Calculate prior period for delta
    const prior = RAW_DATA.filter(r => /* prior period logic */ false);
    const priorTotal = prior.reduce((s, r) => s + r.revenue, 0);
    const delta = total - priorTotal;
    const deltaPct = priorTotal > 0 ? (delta / priorTotal) * 100 : 0;
    return { total, delta, deltaPct, orders: data.reduce((s, r) => s + r.orders, 0) };
  }, [data]);

  // Table data — sorted + paginated
  const tableData = useMemo(() => {
    const sorted = [...data].sort((a, b) => {
      const v = a[sort.col] < b[sort.col] ? -1 : 1;
      return sort.dir === 'asc' ? v : -v;
    });
    return { rows: sorted.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE), total: sorted.length };
  }, [data, sort, page]);

  const handleSort = useCallback((col) => {
    setSort(s => ({ col, dir: s.col === col && s.dir === 'desc' ? 'asc' : 'desc' }));
    setPage(1);
  }, []);

  return (
    <div style={{ fontFamily: 'var(--sans)', background: 'var(--bg)', color: 'var(--text)', minHeight: '100vh' }}>
      <DashHeader onExport={() => exportCSV(data)} />
      <FilterBar region={region} onRegion={r => { setRegion(r); setPage(1); }} />
      <main style={{ padding: 'var(--s8)', display: 'flex', flexDirection: 'column', gap: 'var(--s4)' }}>
        <KPIRow kpis={kpis} data={data} />
        <ChartsRow data={data} />
        <DataTable rows={tableData.rows} total={tableData.total} sort={sort} onSort={handleSort} page={page} onPage={setPage} />
      </main>
    </div>
  );
}
```

## KPI card component

```jsx
function KPICard({ label, value, delta, deltaPct, sparkData, colour }) {
  const isUp = delta >= 0;
  const sign = isUp ? '+' : '';

  return (
    <div style={{
      background: 'var(--surface)', border: '1px solid var(--border)',
      borderRadius: 'var(--r-lg)', padding: 'var(--s5)',
    }}>
      {/* Layer 4: Label */}
      <div style={{ fontFamily: 'var(--mono)', fontSize: 10, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'var(--muted)', marginBottom: 'var(--s3)' }}>
        {label}
      </div>
      {/* Layer 1: Value — font-weight 300, large, light */}
      <div style={{ fontFamily: 'var(--mono)', fontSize: 36, fontWeight: 300, letterSpacing: '-0.03em', lineHeight: 1, marginBottom: 'var(--s2)', fontVariantNumeric: 'tabular-nums' }}>
        {value}
      </div>
      {/* Layer 2: Delta — absolute + relative both */}
      <div style={{ fontFamily: 'var(--mono)', fontSize: 11, color: isUp ? 'var(--good)' : 'var(--bad)', marginBottom: 'var(--s4)', display: 'flex', alignItems: 'center', gap: 4 }}>
        <span>{isUp ? '↑' : '↓'}</span>
        <span>{sign}{fmt(delta)} ({sign}{Math.abs(deltaPct).toFixed(1)}%)</span>
      </div>
      {/* Layer 3: Sparkline */}
      <Sparkline data={sparkData} colour={isUp ? 'var(--good)' : 'var(--bad)'} />
    </div>
  );
}

function Sparkline({ data, colour }) {
  const max = Math.max(...data);
  return (
    <div style={{ height: 28, display: 'flex', alignItems: 'flex-end', gap: 2 }}>
      {data.map((v, i) => (
        <div key={i} style={{
          flex: 1, borderRadius: '1px 1px 0 0',
          height: `${(v / max * 100).toFixed(0)}%`,
          background: colour,
          opacity: v === max ? 0.8 : 0.3,
        }} />
      ))}
    </div>
  );
}
```

## Recharts configs

### Line chart (trend over time)

```jsx
<ResponsiveContainer width="100%" height={220}>
  <AreaChart data={chartData} margin={{ top: 4, right: 4, bottom: 0, left: 0 }}>
    <defs>
      <linearGradient id="fillGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stopColor="var(--accent)" stopOpacity={0.15} />
        <stop offset="100%" stopColor="var(--accent)" stopOpacity={0} />
      </linearGradient>
    </defs>
    <CartesianGrid stroke="var(--border)" strokeDasharray="0" vertical={false} />
    <XAxis dataKey="month" tick={{ fontFamily: 'var(--mono)', fontSize: 10, fill: 'var(--muted)' }} axisLine={false} tickLine={false} />
    <YAxis tick={{ fontFamily: 'var(--mono)', fontSize: 10, fill: 'var(--muted)' }} axisLine={false} tickLine={false} width={48} tickFormatter={n => `$${(n/1000).toFixed(0)}k`} />
    <Tooltip contentStyle={{ background: 'var(--surface2)', border: '1px solid var(--border)', borderRadius: 6, fontFamily: 'var(--mono)', fontSize: 12 }} />
    <Area type="monotone" dataKey="revenue" stroke="var(--accent)" strokeWidth={1.5} fill="url(#fillGrad)" dot={false} activeDot={{ r: 4, fill: 'var(--accent)' }} />
  </AreaChart>
</ResponsiveContainer>
```

### Bar chart (comparison — Y axis starts at zero)

```jsx
<ResponsiveContainer width="100%" height={220}>
  <BarChart data={barData} margin={{ top: 4, right: 4, bottom: 0, left: 0 }} barSize={20}>
    <CartesianGrid stroke="var(--border)" strokeDasharray="0" horizontal={true} vertical={false} />
    <XAxis dataKey="name" tick={{ fontFamily: 'var(--mono)', fontSize: 10, fill: 'var(--muted)' }} axisLine={false} tickLine={false} />
    <YAxis tick={{ fontFamily: 'var(--mono)', fontSize: 10, fill: 'var(--muted)' }} axisLine={false} tickLine={false} domain={[0, 'auto']} />
    <Tooltip contentStyle={{ background: 'var(--surface2)', border: '1px solid var(--border)', borderRadius: 6, fontFamily: 'var(--mono)', fontSize: 12 }} />
    <Bar dataKey="value" fill="var(--accent)" radius={[3, 3, 0, 0]} />
  </BarChart>
</ResponsiveContainer>
```

### Donut chart (max 5 segments)

```jsx
const COLOURS = ['var(--accent)', '#a78bfa', 'var(--good)', '#f59e0b', 'var(--muted)'];

<ResponsiveContainer width="100%" height={180}>
  <PieChart>
    <Pie data={donutData} cx="50%" cy="50%" innerRadius={52} outerRadius={72}
      dataKey="value" paddingAngle={2}>
      {donutData.map((_, i) => <Cell key={i} fill={COLOURS[i % COLOURS.length]} />)}
    </Pie>
    <Tooltip contentStyle={{ background: 'var(--surface2)', border: '1px solid var(--border)', borderRadius: 6, fontFamily: 'var(--mono)', fontSize: 12 }} />
  </PieChart>
</ResponsiveContainer>
```

## Table component

```jsx
function DataTable({ rows, total, sort, onSort, page, onPage }) {
  const pages = Math.ceil(total / 10);
  const cols = [
    { key: 'name',    label: 'Customer', primary: true },
    { key: 'status',  label: 'Status' },
    { key: 'revenue', label: 'Revenue', num: true },
    { key: 'delta',   label: 'MoM', num: true },
  ];

  return (
    <div style={{ background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--r-lg)', overflow: 'hidden' }}>
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            {cols.map(c => (
              <th key={c.key} onClick={() => onSort(c.key)}
                style={{ fontFamily: 'var(--mono)', fontSize: 10, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.1em', color: sort.col === c.key ? 'var(--accent)' : 'var(--muted)', padding: '8px 20px', textAlign: c.num ? 'right' : 'left', borderBottom: '1px solid var(--border)', cursor: 'pointer', userSelect: 'none', whiteSpace: 'nowrap' }}>
                {c.label} {sort.col === c.key ? (sort.dir === 'asc' ? '↑' : '↓') : '↕'}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.length === 0 ? (
            <tr><td colSpan={cols.length} style={{ padding: '40px', textAlign: 'center', color: 'var(--muted)', fontSize: 13 }}>No records match your filters</td></tr>
          ) : rows.map((r, i) => (
            <tr key={i} style={{ borderBottom: '1px solid var(--border)' }}
              onMouseEnter={e => e.currentTarget.style.background = 'var(--surface2)'}
              onMouseLeave={e => e.currentTarget.style.background = ''}>
              <td style={{ padding: '8px 20px', fontWeight: 500, fontSize: 13 }}>{r.name}</td>
              <td style={{ padding: '8px 20px' }}>
                <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontFamily: 'var(--mono)', fontSize: 11, color: 'var(--muted)' }}>
                  <span style={{ width: 6, height: 6, borderRadius: '50%', background: r.status === 'Active' ? 'var(--good)' : r.status === 'Error' ? 'var(--bad)' : 'var(--warn)' }} />
                  {r.status}
                </span>
              </td>
              <td style={{ padding: '8px 20px', fontFamily: 'var(--mono)', fontSize: 12, textAlign: 'right', fontVariantNumeric: 'tabular-nums' }}>{fmt(r.revenue)}</td>
              <td style={{ padding: '8px 20px', fontFamily: 'var(--mono)', fontSize: 11, textAlign: 'right', color: r.delta >= 0 ? 'var(--good)' : 'var(--bad)' }}>
                {r.delta >= 0 ? '↑' : '↓'} {Math.abs(r.deltaPct).toFixed(1)}%
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      {/* Pagination */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 20px', borderTop: '1px solid var(--border)' }}>
        {Array.from({ length: pages }, (_, i) => i + 1).map(n => (
          <button key={n} onClick={() => onPage(n)} style={{ height: 28, minWidth: 28, padding: '0 8px', background: page === n ? 'var(--accent-d)' : 'none', border: `1px solid ${page === n ? 'var(--accent)' : 'var(--border)'}`, borderRadius: 3, fontFamily: 'var(--mono)', fontSize: 11, color: page === n ? 'var(--accent)' : 'var(--muted)', cursor: 'pointer' }}>{n}</button>
        ))}
        <span style={{ marginLeft: 'auto', fontFamily: 'var(--mono)', fontSize: 11, color: 'var(--muted)' }}>
          {total} records
        </span>
      </div>
    </div>
  );
}
```

## CSS variables in React

Inject the preset tokens into the document root. Call this once at app start:

```jsx
function applyPreset(preset) {
  // Import token values from styles.md and set on :root
  const tokens = PRESETS[preset]; // { '--bg': '#080808', ... }
  const root = document.documentElement;
  Object.entries(tokens).forEach(([k, v]) => root.style.setProperty(k, v));
}
// Call: applyPreset('dark-terminal');
```
