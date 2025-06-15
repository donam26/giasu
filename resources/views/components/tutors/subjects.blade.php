@props(['tutor', 'subjects'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Môn học & Cấp học giảng dạy</h3>
        </div>

        <!-- Cấp học -->
        @if($tutor->classLevels && count($tutor->classLevels) > 0)
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Cấp học có thể dạy</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($tutor->classLevels as $classLevel)
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h5 class="text-lg font-medium text-gray-900">{{ $classLevel->name }}</h5>
                                    <p class="text-sm text-gray-600">{{ $classLevel->description }}</p>
                                    @if($classLevel->pivot->price_per_hour && $classLevel->pivot->price_per_hour != $tutor->hourly_rate)
                                        <p class="text-sm font-medium text-blue-600 mt-1">
                                            Giá: @vnd($classLevel->pivot->price_per_hour)/giờ
                                        </p>
                                    @endif
                                    @if($classLevel->pivot->experience_details)
                                        <p class="text-xs text-gray-500 mt-2 italic">
                                            {{ Str::limit($classLevel->pivot->experience_details, 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if($subjects && count($subjects) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($subjects->groupBy('level') as $level => $levelSubjects)
                    <div class="bg-indigo-50 rounded-lg overflow-hidden">
                        <div class="bg-indigo-100 px-4 py-3">
                            <h4 class="font-medium text-indigo-800">{{ $level }}</h4>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                @foreach($levelSubjects as $subject)
                                    <div class="bg-white rounded-lg p-3 border border-indigo-200">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-indigo-800">{{ $subject->name }}</span>
                                            @if($subject->pivot->price_per_hour && $subject->pivot->price_per_hour != $tutor->hourly_rate)
                                                <span class="text-sm font-medium text-green-600">
                                                    @vnd($subject->pivot->price_per_hour)/giờ
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-500">
                                                    @vnd($tutor->hourly_rate)/giờ
                                                </span>
                                            @endif
                                        </div>
                                        @if($subject->pivot->experience_details)
                                            <p class="text-xs text-gray-600 mt-2">
                                                {{ Str::limit($subject->pivot->experience_details, 150) }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 bg-gray-50 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có môn học</h3>
                <p class="mt-1 text-sm text-gray-500">Gia sư chưa cập nhật thông tin môn học giảng dạy.</p>
            </div>
        @endif

        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Phương pháp giảng dạy</h4>
            
            <div class="bg-gray-50 p-5 rounded-lg">
                @if($tutor->teaching_method)
                    <div class="prose max-w-none">
                        {!! nl2br(e($tutor->teaching_method)) !!}
                    </div>
                @else
                    <p class="text-gray-500 italic">Gia sư chưa cập nhật phương pháp giảng dạy.</p>
                @endif
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-md bg-indigo-100 text-indigo-600">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h5 class="text-lg font-medium text-gray-900">Linh hoạt</h5>
                            <p class="mt-2 text-sm text-gray-500">Điều chỉnh phương pháp giảng dạy phù hợp với từng học sinh</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-md bg-indigo-100 text-indigo-600">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h5 class="text-lg font-medium text-gray-900">Hiệu quả</h5>
                            <p class="mt-2 text-sm text-gray-500">Tập trung vào mục tiêu và kết quả học tập cụ thể</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-md bg-indigo-100 text-indigo-600">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h5 class="text-lg font-medium text-gray-900">Tài liệu phong phú</h5>
                            <p class="mt-2 text-sm text-gray-500">Biên soạn và cung cấp tài liệu học tập phù hợp</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 