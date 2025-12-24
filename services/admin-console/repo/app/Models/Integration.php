<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'client_id',
        'provider',
        'name',
        'credentials',
        'status',
        'settings',
    ];

    protected $casts = [
        'provider' => \App\Enums\IntegrationProvider::class,
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'status' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
