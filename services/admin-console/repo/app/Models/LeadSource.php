<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    protected $table = 'lead_sources';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'icon',
        'type',
    ];
}
