<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class AiPrompt extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'lead_ai_prompts';
    public $timestamps = false; // PostgreSQL triggers handle timestamps
    public $incrementing = false; // Using UUIDs

    protected $fillable = [
        'client_id',
        'slug',
        'prompt_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope: Get only global prompts (client_id is NULL)
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    /**
     * Scope: Get prompts for a specific client
     */
    public function scopeForClient(Builder $query, ?string $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope: Get only active prompts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available template variables
     */
    public static function getTemplateVariables(): array
    {
        return [
            '{context_text}' => 'Fragmentos recuperados del sistema RAG',
            '{input}' => 'Entrada del usuario (manejado por LangChain)',
        ];
    }

    /**
     * Validate that prompt contains required variables
     */
    public function hasContextVariable(): bool
    {
        return str_contains($this->prompt_text, '{context_text}');
    }
}
