<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    protected $table = 'lead_properties';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'client_id',
        'property_type_id',
        'assigned_contact_id',
        'title',
        'description',
        'address_street',
        'address_city',
        'address_state',
        'address_zip',
        'location_lat',
        'location_lng',
        'bedrooms',
        'bathrooms',
        'area_sqm',
        'features',
        'price',
        'currency_id',
        'status',
        'external_ref',
        'public_url',
    ];

    protected $casts = [
        'features' => 'array',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'price' => 'decimal:2',
        'bathrooms' => 'decimal:1',
        'area_sqm' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_contact_id');
    }
}
