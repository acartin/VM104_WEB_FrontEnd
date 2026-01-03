<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'lead_conversations';
    public $incrementing = false;

    protected $fillable = [
        'lead_id',
        'platform',
        'conversation_id',
        'messages',
        'summary',
        'sentiment',
        'started_at',
        'ended_at',
        'last_message_at',
        'total_messages',
        'bot_messages',
        'lead_messages',
    ];

    protected $casts = [
        'messages' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_message_at' => 'datetime',
        'total_messages' => 'integer',
        'bot_messages' => 'integer',
        'lead_messages' => 'integer',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Add a message to the conversation
     */
    public function addMessage(string $sender, string $text, string $type = 'text', array $metadata = []): void
    {
        $messages = $this->messages ?? [];
        
        $messages[] = [
            'timestamp' => now()->toIso8601String(),
            'sender' => $sender, // 'bot', 'lead', 'agent'
            'text' => $text,
            'type' => $type, // 'text', 'image', 'audio', 'video'
            'metadata' => $metadata,
        ];

        $this->messages = $messages;
        $this->total_messages = count($messages);
        $this->last_message_at = now();

        if ($sender === 'bot') {
            $this->bot_messages++;
        } elseif ($sender === 'lead') {
            $this->lead_messages++;
        }

        $this->save();
    }

    /**
     * Get the last message
     */
    public function getLastMessage(): ?array
    {
        $messages = $this->messages ?? [];
        return empty($messages) ? null : end($messages);
    }

    /**
     * Get messages by sender
     */
    public function getMessagesBySender(string $sender): array
    {
        $messages = $this->messages ?? [];
        return array_filter($messages, fn($msg) => $msg['sender'] === $sender);
    }

    /**
     * Search messages by text
     */
    public function searchMessages(string $query): array
    {
        $messages = $this->messages ?? [];
        return array_filter($messages, fn($msg) => 
            stripos($msg['text'] ?? '', $query) !== false
        );
    }
}
