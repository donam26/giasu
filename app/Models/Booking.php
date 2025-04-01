<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tutor_id', 
        'subject_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'price_per_hour',
        'total_amount',
        'completed_at',
        'completion_notes',
        'cancelled_reason',
        'cancelled_by',
        'refund_percentage'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
        'price_per_hour' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_percentage' => 'integer'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    
    /**
     * Lấy các thanh toán của buổi học
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
} 