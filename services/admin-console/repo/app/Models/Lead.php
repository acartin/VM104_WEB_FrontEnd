<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    protected $table = 'lead_leads';
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
        'score_total',
        'eng_def_id',
        'fin_def_id',
        'timeline_def_id',
        'match_def_id',
        'info_def_id',
        'priority_def_id',
        'source_property_ref',
        'source_property_url',
        'estimated_value',
        'property_snapshot',
        // UTM Tracking
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'click_id',
        'click_id_type',
        // Session Metadata
        'referrer_url',
        'landing_page_url',
        'user_agent',
        'ip_address',
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
        'estimated_value' => 'decimal:2',
        'property_snapshot' => 'array',
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

    // RELACIONES DE SCORING (MOTOR)
    public function engagementDef() { return $this->belongsTo(ScoringDefinition::class, 'eng_def_id'); }
    public function financeDef() { return $this->belongsTo(ScoringDefinition::class, 'fin_def_id'); }
    public function timelineDef() { return $this->belongsTo(ScoringDefinition::class, 'timeline_def_id'); }
    public function matchDef() { return $this->belongsTo(ScoringDefinition::class, 'match_def_id'); }
    public function infoDef() { return $this->belongsTo(ScoringDefinition::class, 'info_def_id'); }
    public function priorityDef() { return $this->belongsTo(ScoringDefinition::class, 'priority_def_id'); }

    // CONVERSACIONES
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }


    /**
     * Devuelve el significado cualitativo de cada criterio basado en las relaciones normalizadas
     */
    public function getThermalAnalysis(): array
    {
        return [
            'engagement' => $this->engagementDef?->meaning ?? 'Sin calificación',
            'finance'    => $this->financeDef?->meaning ?? 'Sin calificación',
            'timeline'   => $this->timelineDef?->meaning ?? 'Sin calificación',
            'match'      => $this->matchDef?->meaning ?? 'Sin calificación',
            'info'       => $this->infoDef?->meaning ?? 'Sin calificación',
        ];
    }

    /**
     * Devuelve el Significado de Negocio del Score Total
     */
    public function getPriorityAnalysis(): string
    {
        return $this->priorityDef?->meaning ?? 'Prioridad no definida';
    }
}
