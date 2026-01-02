<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'crm_countries';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'iso_code',
    ];
}
