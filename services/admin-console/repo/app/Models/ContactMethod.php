<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContactMethod extends Model
{
    use HasUuids;

    protected $table = 'lead_contact_methods';
    public $timestamps = false;
    public $incrementing = false;
    // User schema said updated_at timestamptz [default: `now()`]
    
    protected $fillable = [
        'user_id',
        'channel_id',
        'value',
        'label',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channel()
    {
        return $this->belongsTo(CommunicationChannel::class, 'channel_id');
    }
}
