@extends('layouts.tutor')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Thông tin tài khoản cá nhân -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4">{{ __('Thông tin tài khoản') }}</h2>
                
                <form method="POST" action="{{ route('tutor.profile.update-account') }}" class="mb-6">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Họ tên -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Họ và tên') }}</label>
                            <input id="name" name="name" type="text" value="{{ old('name', Auth::user()->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', Auth::user()->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('Cập nhật thông tin tài khoản') }}
                        </button>
                    </div>
                </form>
                
                <!-- Đổi mật khẩu -->
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Đổi mật khẩu') }}</h3>
                <form method="POST" action="{{ route('tutor.profile.update-password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Mật khẩu hiện tại -->
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">{{ __('Mật khẩu hiện tại') }}</label>
                            <input id="current_password" name="current_password" type="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('current_password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Mật khẩu mới -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Mật khẩu mới') }}</label>
                            <input id="password" name="password" type="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Xác nhận mật khẩu mới -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Xác nhận mật khẩu mới') }}</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('Đổi mật khẩu') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Thông tin Gia Sư -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4">{{ __('Thông tin Gia Sư') }}</h2>

                <form method="POST" action="{{ route('tutor.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Trình độ học vấn -->
                        <div>
                            <label for="education_level" class="block text-sm font-medium text-gray-700">{{ __('Trình độ học vấn') }}</label>
                            <select id="education_level" name="education_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="Đại học" @if($tutor->education_level === 'Đại học') selected @endif>Đại học</option>
                                <option value="Cao đẳng" @if($tutor->education_level === 'Cao đẳng') selected @endif>Cao đẳng</option>
                                <option value="Thạc sĩ" @if($tutor->education_level === 'Thạc sĩ') selected @endif>Thạc sĩ</option>
                                <option value="Tiến sĩ" @if($tutor->education_level === 'Tiến sĩ') selected @endif>Tiến sĩ</option>
                            </select>
                            @error('education_level')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Trường đại học -->
                        <div>
                            <label for="university" class="block text-sm font-medium text-gray-700">{{ __('Trường đại học') }}</label>
                            <input type="text" name="university" id="university" value="{{ old('university', $tutor->university) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('university')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Chuyên ngành -->
                        <div>
                            <label for="major" class="block text-sm font-medium text-gray-700">{{ __('Chuyên ngành') }}</label>
                            <input type="text" name="major" id="major" value="{{ old('major', $tutor->major) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('major')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kinh nghiệm dạy học -->
                        <div>
                            <label for="teaching_experience" class="block text-sm font-medium text-gray-700">{{ __('Kinh nghiệm dạy học (năm)') }}</label>
                            <input type="number" name="teaching_experience" id="teaching_experience" value="{{ old('teaching_experience', $tutor->teaching_experience) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('teaching_experience')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Giá mỗi giờ -->
                        <div>
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700">{{ __('Giá mỗi giờ mặc định (VNĐ)') }}</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $tutor->hourly_rate) }}"
                                    class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 sm:text-sm">VNĐ</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Giá hiện tại: @vnd($tutor->hourly_rate)</p>
                            @error('hourly_rate')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ảnh đại diện -->
                        <div>
                            <label for="avatar" class="block text-sm font-medium text-gray-700">{{ __('Ảnh đại diện') }}</label>
                            @if($tutor->avatar)
                                <div class="mb-4">
                                    <img src="{{ asset('storage/' . $tutor->avatar) }}" alt="Avatar" class="w-32 h-32 object-cover rounded-full">
                                </div>
                            @endif
                            <input type="file" name="avatar" id="avatar"
                                class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100">
                            @error('avatar')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Giới thiệu -->
                    <div class="mt-4">
                        <label for="bio" class="block text-sm font-medium text-gray-700">{{ __('Giới thiệu') }}</label>
                        <textarea name="bio" id="bio" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('bio', $tutor->bio) }}</textarea>
                        @error('bio')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cấp học -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Cấp học có thể dạy</h3>
                        <p class="text-sm text-gray-600 mb-4">Chọn các cấp học mà bạn có thể giảng dạy</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($allClassLevels as $classLevel)
                                <div class="flex items-start p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center h-5">
                                        <input id="class_level_{{ $classLevel->id }}" name="class_levels[]" value="{{ $classLevel->id }}" 
                                            type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            {{ $tutor->classLevels->contains($classLevel->id) ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3">
                                        <label for="class_level_{{ $classLevel->id }}" class="text-sm font-medium text-gray-700">{{ $classLevel->name }}</label>
                                        <p class="text-xs text-gray-500 mt-1">{{ $classLevel->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('class_levels')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Môn học -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Môn học dạy</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach(App\Models\Subject::where('is_active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category') as $category => $subjectGroup)
                                <div class="space-y-2">
                                    <h4 class="font-medium text-gray-700">{{ $category }}</h4>
                                    @foreach($subjectGroup as $subject)
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="subject_{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}" 
                                                    type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    {{ $tutor->subjects->contains($subject->id) ? 'checked' : '' }}>
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="subject_{{ $subject->id }}" class="font-medium text-gray-700">{{ $subject->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        @error('subjects')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quản lý giá cho từng cấp học -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Quản lý giá cho từng cấp học</h3>
                        <p class="text-sm text-gray-600 mb-4">Thiết lập giá cụ thể cho từng cấp học. Nếu để trống, hệ thống sẽ sử dụng giá mặc định.</p>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cấp học</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá mỗi giờ (VNĐ)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi tiết kinh nghiệm</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="class-level-prices-container">
                                    @foreach($tutor->classLevels as $classLevel)
                                        <tr class="class-level-price-row" data-class-level-id="{{ $classLevel->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $classLevel->name }}
                                                <p class="text-xs text-gray-500">{{ $classLevel->description }}</p>
                                                <input type="hidden" name="class_level_prices[{{ $classLevel->id }}][id]" value="{{ $classLevel->id }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="relative rounded-md shadow-sm">
                                                    <input type="number" name="class_level_prices[{{ $classLevel->id }}][price]" 
                                                        value="{{ $classLevel->pivot->price_per_hour }}" 
                                                        class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        placeholder="Giá mặc định">
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <span class="text-gray-500 sm:text-sm">VNĐ</span>
                                                    </div>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">Giá hiện tại: @vnd($classLevel->pivot->price_per_hour)</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <textarea name="class_level_prices[{{ $classLevel->id }}][experience]" rows="2"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="Chi tiết kinh nghiệm giảng dạy ở cấp học này">{{ $classLevel->pivot->experience_details }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quản lý giá cho từng môn học -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Quản lý giá cho từng môn học</h3>
                        <p class="text-sm text-gray-600 mb-4">Thiết lập giá cụ thể cho từng môn học. Nếu để trống, hệ thống sẽ sử dụng giá mặc định.</p>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Môn học</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá mỗi giờ (VNĐ)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi tiết kinh nghiệm</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="subject-prices-container">
                                    @foreach($tutor->subjects as $subject)
                                        <tr class="subject-price-row" data-subject-id="{{ $subject->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $subject->name }} ({{ $subject->category }})
                                                <input type="hidden" name="subject_prices[{{ $subject->id }}][id]" value="{{ $subject->id }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="relative rounded-md shadow-sm">
                                                    <input type="number" name="subject_prices[{{ $subject->id }}][price]" 
                                                        value="{{ $subject->pivot->price_per_hour }}" 
                                                        class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        placeholder="Giá mặc định">
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <span class="text-gray-500 sm:text-sm">VNĐ</span>
                                                    </div>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">Giá hiện tại: @vnd($subject->pivot->price_per_hour)</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <textarea name="subject_prices[{{ $subject->id }}][experience]" rows="2"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="Chi tiết kinh nghiệm giảng dạy môn này">{{ $subject->pivot->experience_details }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('Cập nhật thông tin') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Script để cập nhật bảng giá môn học và cấp học khi chọn/bỏ chọn
    document.addEventListener('DOMContentLoaded', function() {
        const subjectCheckboxes = document.querySelectorAll('input[name="subjects[]"]');
        const priceContainer = document.getElementById('subject-prices-container');
        const classLevelCheckboxes = document.querySelectorAll('input[name="class_levels[]"]');
        const classLevelPriceContainer = document.getElementById('class-level-prices-container');
        
        // Cập nhật hiển thị bảng giá
        function updatePriceTable() {
            // Lấy giá mặc định hiện tại
            const defaultPrice = document.getElementById('hourly_rate').value || 0;
            
            // Xóa tất cả các hàng hiện tại và tạo lại bảng
            priceContainer.innerHTML = '';
            
            // Thêm lại các hàng cho các môn học đã chọn
            subjectCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const subjectId = checkbox.value;
                    const subjectLabel = document.querySelector(`label[for="subject_${subjectId}"]`);
                    const subjectName = subjectLabel ? subjectLabel.textContent.trim() : `Môn học ${subjectId}`;
                    const categoryName = subjectLabel.closest('.space-y-2').querySelector('h4').textContent.trim();
                    
                    // Tìm giá và mô tả kinh nghiệm hiện tại (nếu có)
                    let existingPrice = defaultPrice;
                    let existingExperience = '';
                    
                    const existingPriceInput = document.querySelector(`input[name="subject_prices[${subjectId}][price]"]`);
                    if (existingPriceInput) {
                        existingPrice = existingPriceInput.value;
                    }
                    
                    const existingExpTextarea = document.querySelector(`textarea[name="subject_prices[${subjectId}][experience]"]`);
                    if (existingExpTextarea) {
                        existingExperience = existingExpTextarea.value;
                    }
                    
                    // Tạo hàng mới
                    createNewPriceRow(subjectId, subjectName, categoryName, existingPrice, existingExperience);
                }
            });
        }

        // Tạo hàng mới cho môn học đã chọn
        function createNewPriceRow(subjectId, subjectName, categoryName, price, experience) {
            const newRow = document.createElement('tr');
            newRow.className = 'subject-price-row';
            newRow.dataset.subjectId = subjectId;
            
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${subjectName} (${categoryName})
                    <input type="hidden" name="subject_prices[${subjectId}][id]" value="${subjectId}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="relative rounded-md shadow-sm">
                        <input type="number" name="subject_prices[${subjectId}][price]" 
                            value="${price}" 
                            class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Giá mặc định">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 sm:text-sm">VNĐ</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <textarea name="subject_prices[${subjectId}][experience]" rows="2"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Chi tiết kinh nghiệm giảng dạy môn này">${experience}</textarea>
                </td>
            `;
            
            priceContainer.appendChild(newRow);
        }

        // Cập nhật hiển thị bảng giá cấp học
        function updateClassLevelPriceTable() {
            // Lấy giá mặc định hiện tại
            const defaultPrice = document.getElementById('hourly_rate').value || 0;
            
            // Xóa tất cả các hàng hiện tại và tạo lại bảng
            classLevelPriceContainer.innerHTML = '';
            
            // Thêm lại các hàng cho các cấp học đã chọn
            classLevelCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const classLevelId = checkbox.value;
                    const classLevelLabel = document.querySelector(`label[for="class_level_${classLevelId}"]`);
                    const classLevelName = classLevelLabel ? classLevelLabel.textContent.trim() : `Cấp học ${classLevelId}`;
                    const classLevelDesc = classLevelLabel ? classLevelLabel.parentNode.querySelector('p').textContent.trim() : '';
                    
                    // Tìm giá và mô tả kinh nghiệm hiện tại (nếu có)
                    let existingPrice = defaultPrice;
                    let existingExperience = '';
                    
                    const existingPriceInput = document.querySelector(`input[name="class_level_prices[${classLevelId}][price]"]`);
                    if (existingPriceInput) {
                        existingPrice = existingPriceInput.value;
                    }
                    
                    const existingExpTextarea = document.querySelector(`textarea[name="class_level_prices[${classLevelId}][experience]"]`);
                    if (existingExpTextarea) {
                        existingExperience = existingExpTextarea.value;
                    }
                    
                    // Tạo hàng mới
                    createNewClassLevelPriceRow(classLevelId, classLevelName, classLevelDesc, existingPrice, existingExperience);
                }
            });
        }

        // Tạo hàng mới cho cấp học đã chọn
        function createNewClassLevelPriceRow(classLevelId, classLevelName, classLevelDesc, price, experience) {
            const newRow = document.createElement('tr');
            newRow.className = 'class-level-price-row';
            newRow.dataset.classLevelId = classLevelId;
            
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${classLevelName}
                    <p class="text-xs text-gray-500">${classLevelDesc}</p>
                    <input type="hidden" name="class_level_prices[${classLevelId}][id]" value="${classLevelId}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="relative rounded-md shadow-sm">
                        <input type="number" name="class_level_prices[${classLevelId}][price]" 
                            value="${price}" 
                            class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Giá mặc định">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 sm:text-sm">VNĐ</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <textarea name="class_level_prices[${classLevelId}][experience]" rows="2"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Chi tiết kinh nghiệm giảng dạy ở cấp học này">${experience}</textarea>
                </td>
            `;
            
            classLevelPriceContainer.appendChild(newRow);
        }

        // Khởi tạo bảng giá
        updatePriceTable();
        updateClassLevelPriceTable();

        // Lắng nghe sự kiện thay đổi trên các checkbox môn học
        subjectCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updatePriceTable);
        });

        // Lắng nghe sự kiện thay đổi trên các checkbox cấp học
        classLevelCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateClassLevelPriceTable);
        });
        
        // Lắng nghe sự kiện thay đổi giá mặc định
        document.getElementById('hourly_rate').addEventListener('change', function() {
            // Cập nhật giá mặc định cho các môn chưa được thiết lập giá
            const emptyPriceInputs = document.querySelectorAll('input[name^="subject_prices"][name$="[price]"], input[name^="class_level_prices"][name$="[price]"]');
            emptyPriceInputs.forEach(input => {
                if (!input.value) {
                    input.value = this.value;
                }
            });
        });
    });
</script>
@endsection 