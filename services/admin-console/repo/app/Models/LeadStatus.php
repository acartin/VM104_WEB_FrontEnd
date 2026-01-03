<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    protected $table = 'lead_statuses';
    public $timestamps = false; // No timestamps in migration

    protected $fillable = [
        'name',
        'key',
        'color',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
