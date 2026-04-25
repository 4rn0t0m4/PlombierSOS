<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'session_id',
        'ip',
        'city',
        'postal_code',
        'messages',
        'message_count',
        'page_url',
    ];

    protected $casts = [
        'messages' => 'array',
    ];
}
