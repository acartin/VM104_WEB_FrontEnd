<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    protected $table = 'crm_leads';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'client_id',
        'source_id',
        'origin_channel_id',
        'assigned_user_id',
        'full_name',
        'email',
        'phone',
        'declared_income',
        'current_debts',
        'financial_currency_id',
        'status', // Legacy
        'status_id',
        'score_engagement',
        'score_finance',
        'score_timeline',
        'score_match',
        'score_info',
        'eng_icon',
        'eng_color',
        'fin_icon',
        'fin_color',
        'timeline_label',
        'timeline_color',
        'match_icon',
        'match_color',
        'info_icon',
        'info_color',
    ];

    protected $casts = [
        'declared_income' => 'decimal:2',
        'current_debts' => 'decimal:2',
        'score_engagement' => 'integer',
        'score_finance' => 'integer',
        'score_timeline' => 'integer',
        'score_match' => 'integer',
        'score_info' => 'integer',
        'score_total' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }
}
