<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tutor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'education_level',
        'university',
        'major',
        'teaching_experience',
        'bio',
        'avatar',
        'certification_files',
        'hourly_rate',
        'is_verified',
        'status',
        'teaching_locations',
        'can_teach_online',
        'total_teaching_hours',
        'rating'
    ];

    protected $casts = [
        'teaching_locations' => 'array',
        'is_verified' => 'boolean',
        'can_teach_online' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'tutor_subjects')
            ->withPivot('price_per_hour', 'experience_details')
            ->withTimestamps();
    }

    public function classLevels()
    {
        return $this->belongsToMany(ClassLevel::class, 'tutor_class_levels')
            ->withPivot('price_per_hour', 'experience_details')
            ->withTimestamps();
    }

    public function recommendations()
    {
        return $this->hasMany(AiRecommendation::class);
    }
}
