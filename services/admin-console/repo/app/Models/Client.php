<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Client extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'balance',
        'currency',
        'billing_settings',
    ];

    protected $casts = [
        'billing_settings' => 'array',
        'balance' => 'decimal:2',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
