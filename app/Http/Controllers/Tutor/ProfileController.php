<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;

class ProfileController extends Controller
{
    public function edit()
    {
        $tutor = Auth::user()->tutor->load('subjects');
        
        return view('tutor.profile.edit', [
            'tutor' => $tutor
        ]);
    }

    public function update(Request $request)
    {
        $tutor = Auth::user()->tutor;
        
        $validated = $request->validate([
            'education_level' => 'required|string|max:255',
            'university' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'teaching_experience' => 'required|string',
            'bio' => 'required|string',
            'avatar' => 'nullable|image|max:1024',
            'hourly_rate' => 'required|numeric|min:0',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'exists:subjects,id',
            'subject_prices' => 'nullable|array',
            'subject_prices.*' => 'nullable|array',
            'subject_prices.*.price' => 'nullable|numeric|min:0',
            'subject_prices.*.experience' => 'nullable|string',
        ], [
            'education_level.required' => 'Trình độ học vấn không được bỏ trống',
            'education_level.max' => 'Trình độ học vấn không được vượt quá 255 ký tự',
            'teaching_experience.required' => 'Kinh nghiệm giảng dạy không được bỏ trống',
            'bio.required' => 'Giới thiệu bản thân không được bỏ trống',
            'avatar.image' => 'Ảnh đại diện phải là một hình ảnh',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 1MB',
            'hourly_rate.required' => 'Giá theo giờ không được bỏ trống',
            'hourly_rate.numeric' => 'Giá theo giờ phải là một số',
            'hourly_rate.min' => 'Giá theo giờ phải lớn hơn hoặc bằng 0',
            'subjects.required' => 'Bạn phải chọn ít nhất một môn học',
            'subjects.min' => 'Bạn phải chọn ít nhất một môn học',
            'subjects.*.exists' => 'Môn học đã chọn không hợp lệ',
            'subject_prices.*.price.numeric' => 'Giá theo giờ cho môn học phải là một số',
            'subject_prices.*.price.min' => 'Giá theo giờ cho môn học phải lớn hơn hoặc bằng 0',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Cập nhật thông tin cơ bản
        $tutor->update([
            'education_level' => $validated['education_level'],
            'university' => $validated['university'],
            'major' => $validated['major'],
            'teaching_experience' => $validated['teaching_experience'],
            'bio' => $validated['bio'],
            'hourly_rate' => $validated['hourly_rate'],
        ]);

        if ($request->hasFile('avatar')) {
            $tutor->avatar = $validated['avatar'];
            $tutor->save();
        }

        // Cập nhật môn học và giá cho từng môn học
        if ($request->has('subjects')) {
            $syncData = [];
            
            // Lấy danh sách môn học đã chọn
            $subjectIds = $request->subjects;
            
            // Xử lý dữ liệu giá cho từng môn học
            foreach ($subjectIds as $subjectId) {
                $pricePerHour = $tutor->hourly_rate; // Giá mặc định
                $experienceDetails = null;
                
                // Nếu có thông tin giá cho môn học này
                if (isset($request->subject_prices[$subjectId])) {
                    // Nếu có thiết lập giá cụ thể, sử dụng giá đó
                    if (isset($request->subject_prices[$subjectId]['price']) && 
                        is_numeric($request->subject_prices[$subjectId]['price']) && 
                        $request->subject_prices[$subjectId]['price'] > 0) {
                        $pricePerHour = $request->subject_prices[$subjectId]['price'];
                    }
                    
                    // Lưu chi tiết kinh nghiệm
                    if (isset($request->subject_prices[$subjectId]['experience'])) {
                        $experienceDetails = $request->subject_prices[$subjectId]['experience'];
                    }
                }
                
                $syncData[$subjectId] = [
                    'price_per_hour' => $pricePerHour,
                    'experience_details' => $experienceDetails
                ];
            }
            
            // Đồng bộ hóa dữ liệu môn học
            $tutor->subjects()->sync($syncData);

            // Lưu dữ liệu và đánh dấu quan hệ đã được tải lại
            $tutor->load('subjects');
        }

        return back()->with('success', 'Thông tin gia sư đã được cập nhật thành công.');
    }
} 