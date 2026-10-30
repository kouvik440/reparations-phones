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
        $history = Reparation::query()->whereBetween('date_depot', [$today->copy()->subYearNoOverflow()->startOfYear()->toDateString(), $today->toDateString()])
            ->selectRaw('date_depot, COUNT(*) as total')->groupBy('date_depot')->pluck('total', 'date_depot');
        $evolutions = [];
        foreach (['month', 'year'] as $period) {
            $from = $period === 'month' ? $today->copy()->startOfMonth() : $today->copy()->startOfYear();
            $previousEnd = $period === 'month' ? $today->copy()->subMonthNoOverflow() : $today->copy()->subYearNoOverflow();
            $previousStart = $period === 'month' ? $previousEnd->copy()->startOfMonth() : $previousEnd->copy()->startOfYear();
            $total = 0;
            $prior = 0;
            $points = [];
            for ($day = $from->copy(); $day->lte($today); $day->addDay()) {
                $key = $period === 'month' ? $day->toDateString() : $day->format('Y-m');
                $count = (int) $history->get($day->toDateString(), 0);
                $total += $count;
                if (!isset($points[$key])) {
                    $points[$key] = ['date' => $key, 'label' => $period === 'month' ? $day->format('d') : $day->locale('fr')->isoFormat('MMM'), 'full_label' => $period === 'month' ? $day->locale('fr')->isoFormat('D MMMM YYYY') : $day->locale('fr')->isoFormat('MMMM YYYY'), 'count' => 0, 'today' => $period === 'month' ? $day->isSameDay($today) : $day->month === $today->month];
                }
                $points[$key]['count'] += $count;
            }
            for ($day = $previousStart->copy(); $day->lte($previousEnd); $day->addDay()) {
                $prior += (int) $history->get($day->toDateString(), 0);
            }
            $evolutions[$period] = ['from' => $from->toDateString(), 'to' => $today->toDateString(), 'previousFrom' => $previousStart->toDateString(), 'previousTo' => $previousEnd->toDateString(), 'total' => $total, 'previous' => $prior, 'difference' => $total - $prior, 'percentage' => $prior > 0 ? round(($total - $prior) / $prior * 100, 1) : null, 'days' => array_values($points), 'chartMax' => max(1, ...array_column($points, 'count'))];
        }

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
            'previousWeekTotal' => $previousTotal,
            'weekPercentage' => $previousTotal > 0 ? round(($weekTotal - $previousTotal) / $previousTotal * 100, 1) : null,
            'evolutions' => $evolutions,
            'recentRepairs' => auth()->user()->can('repairs.view') ? Reparation::query()->with('client:id,prenom,nom')->orderByDesc('date_depot')->orderByDesc('id')->limit(5)->get() : collect(),
        ];
    }
}
