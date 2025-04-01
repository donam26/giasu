<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorAvailability extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Các thuộc tính có thể gán giá trị hàng loạt
     *
     * @var array
     */
    protected $fillable = [
        'tutor_id',
        'day_of_week',
        'date',
        'start_time',
        'end_time',
        'is_recurring',
        'status',
    ];

    /**
     * Định nghĩa các thuộc tính cần chuyển đổi kiểu dữ liệu
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Lấy gia sư sở hữu lịch rảnh này
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Kiểm tra xem một lịch rảnh có trùng với lịch học đã đặt không
     */
    public function hasBookingConflicts()
    {
        $tutor = $this->tutor;
        
        return Booking::where('tutor_id', $tutor->id)
            ->where('status', '!=', 'cancelled')
            ->where('day_of_week', $this->day_of_week)
            ->where(function($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function($q) {
                        $q->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>=', $this->end_time);
                    });
            })->exists();
    }

    /**
     * Lấy tên ngày trong tuần theo tiếng Việt
     */
    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        ];

        return $days[$this->day_of_week] ?? 'Không xác định';
    }

    /**
     * Lấy khoảng thời gian đẹp (8:00 - 10:00)
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
