<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContactPreference extends Model
{
    use HasUuids;

    protected $table = 'lead_contact_preferences';
    
    // Postgres handles these via defaults in the migration
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'contact_preference_id');
    }
}
