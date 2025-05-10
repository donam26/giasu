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
        'refund_percentage',
        'reschedule_requested',
        'rescheduled_at',
        'rescheduled_reason',
        'admin_notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'price_per_hour' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_percentage' => 'integer',
        'reschedule_requested' => 'boolean'
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

    /**
     * Lấy các yêu cầu đổi lịch của buổi học
     */
    public function rescheduleRequests()
    {
        return $this->hasMany(BookingRescheduleRequest::class);
    }
    
    /**
     * Lấy yêu cầu đổi lịch mới nhất
     */
    public function latestRescheduleRequest()
    {
        return $this->rescheduleRequests()->latest()->first();
    }
    
    /**
     * Kiểm tra xem có yêu cầu đổi lịch đang chờ không
     */
    public function hasPendingRescheduleRequest()
    {
        // Kiểm tra cả trường cơ bản và database
        if ($this->reschedule_requested) {
            return true;
        }
        
        // Kiểm tra trong database xem có yêu cầu đổi lịch nào đang pending không
        return $this->rescheduleRequests()->where('status', 'pending')->exists();
    }
} 