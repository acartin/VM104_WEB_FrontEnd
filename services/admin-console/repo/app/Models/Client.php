<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Client extends Model
{
    use HasFactory, HasUuids, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'crm_clients';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'country_id',
    ];

    protected $casts = [
        'country_id' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'crm_client_user');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    // Relationship removed as Contact model was consolidated into User

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
