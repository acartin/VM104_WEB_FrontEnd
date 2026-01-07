<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadStatus extends Model
{
    use HasUuids;

    protected $table = 'lead_statuses';
    
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'key',
        'name',
        'icon',
        'color',
        'order',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'status_id');
    }
}
