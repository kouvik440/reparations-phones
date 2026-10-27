<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairStatusChange extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'actor_name', 'from_status', 'to_status', 'version', 'created_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
