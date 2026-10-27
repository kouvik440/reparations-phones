<?php

namespace App\Actions\Reparations;

use App\Enums\RepairStatus;
use App\Models\Reparation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ChangeRepairStatus
{
    public function handle(int $id, RepairStatus $target, int $version, User $actor): Reparation
    {
        return DB::transaction(function () use ($id, $target, $version, $actor): Reparation {
            $repair = Reparation::query()->lockForUpdate()->findOrFail($id);
            if ($repair->status_version !== $version) {
                throw new ConflictHttpException('Ce dossier a été modifié depuis son affichage. Actualisez la page avant de changer le statut.');
            }
            $current = RepairStatus::from($repair->statut);
            // A repeated selection does not create a fictional change.
            if ($current === $target) {
                return $repair;
            }
            if ($current->next() !== $target) {
                throw ValidationException::withMessages(['statut' => 'Transition non autorisée. Suivez les étapes : en attente, en cours, terminée, puis restituée.']);
            }
            $repair->statut = $target->value;
            $repair->status_version++;
            $repair->save();
            $repair->statusChanges()->create([
                'user_id' => $actor->id,
                'actor_name' => $actor->name,
                'from_status' => $current->value,
                'to_status' => $target->value,
                'version' => $repair->status_version,
            ]);

            return $repair;
        }, 3);
    }
}
