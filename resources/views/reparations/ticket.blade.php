<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><style>
@page { margin: 16pt; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; line-height: 1.5; color: #111; }
h1 { font-size: 13pt; margin: 0 0 2pt; } p { margin: 6pt 0; }
.code { font-family: 'DejaVu Sans Mono', monospace; font-size: 22pt; font-weight: bold; margin: 12pt 0; letter-spacing: 1pt; }
.label { font-size: 8pt; color: #444; } .device { font-weight: bold; font-size: 8pt; word-wrap: break-word; }
.url { font-size: 8pt; word-wrap: break-word; } .note { font-size: 7pt; margin-top: 10pt; }
</style></head><body>
<h1>Atelier Réparations</h1><p>Ticket de dépôt</p>
<p class="label">Votre code de suivi personnel</p><div class="code">{{ $repair->code }}</div>
<p class="device">{{ \Illuminate\Support\Str::limit($repair->marque.' '.$repair->modele, 60) }}</p>
<p>Dépôt : {{ \Carbon\Carbon::parse($repair->date_depot)->format('d/m/Y') }}</p>
<p>Suivez votre réparation sur :</p><p class="url">{{ route('suivi') }}</p>
<p class="note">Conservez ce ticket pour le suivi et le retrait de votre appareil. Ce document ne constitue pas une preuve de paiement.</p>
</body></html>
