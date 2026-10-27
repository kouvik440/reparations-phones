<?php

namespace App\Actions\Dashboard;

use App\Models\Client;
use App\Models\Reparation;

final class GetDashboardData
{
    public function handle(): array
    {
        $today = today();
        $start = $today->copy()->subDays(6);
        $counts = Reparation::query()->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut');
        $statuses = [
            'en_attente' => ['label' => 'En attente', 'color' => '#f0b44d', 'count' => (int) $counts->get('en_attente', 0)],
            'en_cours' => ['label' => 'En cours', 'color' => '#487fcb', 'count' => (int) $counts->get('en_cours', 0)],
            'termine' => ['label' => 'À restituer', 'color' => '#148775', 'count' => (int) $counts->get('termine', 0)],
            'restitue' => ['label' => 'Restituées', 'color' => '#64748b', 'count' => (int) $counts->get('restitue', 0)],
        ];
        $daily = Reparation::query()->whereBetween('date_depot', [$start->toDateString(), $today->toDateString()])
            ->selectRaw('date_depot, COUNT(*) as total')->groupBy('date_depot')->pluck('total', 'date_depot');
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $days[] = ['date' => $date->toDateString(), 'label' => $date->locale('fr')->isoFormat('ddd'), 'full_label' => $date->locale('fr')->isoFormat('D MMMM'), 'count' => (int) $daily->get($date->toDateString(), 0), 'today' => $date->isSameDay($today)];
        }
        $weekTotal = array_sum(array_column($days, 'count'));
        $previousTotal = Reparation::query()->whereBetween('date_depot', [$start->copy()->subDays(7)->toDateString(), $start->copy()->subDay()->toDateString()])->count();

        return [
            'date' => $today,
            'statuses' => $statuses,
            'totalRepairs' => (int) $counts->sum(),
            'totalClients' => Client::count(),
            'todayDeposits' => $days[6]['count'],
            'days' => $days,
            'chartMax' => max(1, ...array_column($days, 'count')),
            'weekTotal' => $weekTotal,
            'weekDifference' => $weekTotal - $previousTotal,
            'recentRepairs' => auth()->user()->can('repairs.view') ? Reparation::query()->with('client:id,prenom,nom')->orderByDesc('date_depot')->orderByDesc('id')->limit(5)->get() : collect(),
        ];
    }
}
