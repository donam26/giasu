<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'conversation_type',
        'status',
        'context_data',
        'last_interaction'
    ];

    protected $casts = [
        'context_data' => 'array',
        'last_interaction' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    public function recommendations()
    {
        return $this->hasMany(AiRecommendation::class, 'conversation_id');
    }
}
