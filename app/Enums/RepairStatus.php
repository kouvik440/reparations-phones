<?php

namespace App\Enums;

enum RepairStatus: string
{
    case Waiting = 'en_attente';
    case Working = 'en_cours';
    case Completed = 'termine';
    case Delivered = 'restitue';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'En attente',
            self::Working => 'En cours',
            self::Completed => 'Terminée · à restituer',
            self::Delivered => 'Restituée',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Waiting => self::Working,
            self::Working => self::Completed,
            self::Completed => self::Delivered,
            self::Delivered => null,
        };
    }

    public static function labels(): array
    {
        return array_column(array_map(fn (self $status): array => ['value' => $status->value, 'label' => $status->label()], self::cases()), 'label', 'value');
    }
}
