<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><style>
@page { margin: 4pt; }
body { margin: 0; color: #000; font-family: 'DejaVu Sans', sans-serif; line-height: 1.15; }
.heading { font-size: 6pt; text-transform: uppercase; letter-spacing: .6pt; }
.code { font-family: 'DejaVu Sans Mono', monospace; font-size: 15pt; font-weight: bold; margin: 2pt 0; }
.device { font-size: 7pt; font-weight: bold; height: 15pt; overflow: hidden; overflow-wrap: break-word; }
.date { border-top: .5pt solid #000; padding-top: 2pt; margin-top: 2pt; font-size: 6pt; }
</style></head><body>
<div class="heading">Réparation · Suivi</div>
<div class="code">{{ $repair->code }}</div>
<div class="device">{{ \Illuminate\Support\Str::limit($repair->marque.' '.$repair->modele, 38) }}</div>
<div class="date">Dépôt : {{ \Carbon\Carbon::parse($repair->date_depot)->format('d/m/Y') }}</div>
</body></html>
