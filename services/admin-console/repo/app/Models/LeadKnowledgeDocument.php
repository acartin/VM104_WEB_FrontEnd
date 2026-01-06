<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadKnowledgeDocument extends Model
{
    use HasFactory;

    protected $table = 'lead_knowledge_documents';
    
    // Postgres triggers handle timestamps, but we want Eloquent to ignore them
    public $timestamps = false;
    
    // ID is BIGSERIAL, so it is incrementing and integer
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'client_id',
        'filename',
        'storage_path',
        'content_hash',
        'sync_status',
        'last_synced_at',
        'error_message',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'id' => 'integer',
        // Start status is PENDING by default in controller/service logic, but casting helps
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
