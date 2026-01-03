<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadPropertyType extends Model
{
    protected $table = 'lead_property_types';
    public $timestamps = false;
    protected $fillable = ['name'];
}
