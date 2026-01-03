<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringDefinition extends Model
{
    protected $table = 'lead_scoring_definitions';
    
    public $timestamps = false;

    protected $fillable = [
        'criterion',
        'min_score',
        'max_score',
        'label',
        'meaning',
        'icon',
        'color',
        'is_active',
    ];
}
