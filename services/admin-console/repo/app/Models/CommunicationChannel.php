<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationChannel extends Model
{
    protected $table = 'lead_communication_channels';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'icon',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function methods()
    {
        return $this->hasMany(ContactMethod::class, 'channel_id');
    }
}
