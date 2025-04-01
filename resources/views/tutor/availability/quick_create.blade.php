@extends('layouts.tutor')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Tạo Nhanh Lịch Rảnh') }}</h2>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Tính năng này cho phép bạn tạo nhanh lịch dạy hàng tuần theo các mẫu phổ biến. Các lịch được tạo sẽ tự động lặp lại mỗi tuần vào những ngày đã chọn.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('tutor.availability.quick-store') }}">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Chọn mẫu lịch dạy</label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="relative border rounded-lg p-4 cursor-pointer hover:bg-indigo-50 hover:border-indigo-300" onclick="selectPattern('weekday_mornings')">
                                <input type="radio" id="weekday_mornings" name="pattern" value="weekday_mornings" class="hidden peer">
                                <label for="weekday_mornings" class="flex flex-col cursor-pointer h-full">
                                    <span class="text-lg font-medium mb-2">Buổi sáng các ngày trong tuần</span>
                                    <span class="text-sm text-gray-500">Thứ 2 - Thứ 6, 08:00 - 11:00</span>
                                </label>
                                <svg class="absolute hidden top-2 right-2 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            
                            <div class="relative border rounded-lg p-4 cursor-pointer hover:bg-indigo-50 hover:border-indigo-300" onclick="selectPattern('weekday_evenings')">
                                <input type="radio" id="weekday_evenings" name="pattern" value="weekday_evenings" class="hidden peer">
                                <label for="weekday_evenings" class="flex flex-col cursor-pointer h-full">
                                    <span class="text-lg font-medium mb-2">Buổi tối các ngày trong tuần</span>
                                    <span class="text-sm text-gray-500">Thứ 2 - Thứ 6, 18:00 - 21:00</span>
                                </label>
                                <svg class="absolute hidden top-2 right-2 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            
                            <div class="relative border rounded-lg p-4 cursor-pointer hover:bg-indigo-50 hover:border-indigo-300" onclick="selectPattern('weekends')">
                                <input type="radio" id="weekends" name="pattern" value="weekends" class="hidden peer">
                                <label for="weekends" class="flex flex-col cursor-pointer h-full">
                                    <span class="text-lg font-medium mb-2">Cuối tuần</span>
                                    <span class="text-sm text-gray-500">Thứ 7 - Chủ Nhật, 09:00 - 17:00</span>
                                </label>
                                <svg class="absolute hidden top-2 right-2 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            
                            <div class="relative border rounded-lg p-4 cursor-pointer hover:bg-indigo-50 hover:border-indigo-300" onclick="selectPattern('custom')">
                                <input type="radio" id="custom" name="pattern" value="custom" class="hidden peer">
                                <label for="custom" class="flex flex-col cursor-pointer h-full">
                                    <span class="text-lg font-medium mb-2">Tùy chỉnh</span>
                                    <span class="text-sm text-gray-500">Chọn ngày và giờ tùy ý</span>
                                </label>
                                <svg class="absolute hidden top-2 right-2 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        @error('pattern')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tùy chỉnh ngày trong tuần (chỉ hiển thị nếu chọn "Tùy chỉnh") -->
                    <div id="custom_days_section" class="mb-6 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Chọn ngày trong tuần</label>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <div class="flex items-center">
                                <input id="monday" name="custom_days[]" type="checkbox" value="monday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="monday" class="ml-2 block text-sm text-gray-900">Thứ Hai</label>
                            </div>
                            <div class="flex items-center">
                                <input id="tuesday" name="custom_days[]" type="checkbox" value="tuesday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="tuesday" class="ml-2 block text-sm text-gray-900">Thứ Ba</label>
                            </div>
                            <div class="flex items-center">
                                <input id="wednesday" name="custom_days[]" type="checkbox" value="wednesday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="wednesday" class="ml-2 block text-sm text-gray-900">Thứ Tư</label>
                            </div>
                            <div class="flex items-center">
                                <input id="thursday" name="custom_days[]" type="checkbox" value="thursday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="thursday" class="ml-2 block text-sm text-gray-900">Thứ Năm</label>
                            </div>
                            <div class="flex items-center">
                                <input id="friday" name="custom_days[]" type="checkbox" value="friday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="friday" class="ml-2 block text-sm text-gray-900">Thứ Sáu</label>
                            </div>
                            <div class="flex items-center">
                                <input id="saturday" name="custom_days[]" type="checkbox" value="saturday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="saturday" class="ml-2 block text-sm text-gray-900">Thứ Bảy</label>
                            </div>
                            <div class="flex items-center">
                                <input id="sunday" name="custom_days[]" type="checkbox" value="sunday"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="sunday" class="ml-2 block text-sm text-gray-900">Chủ Nhật</label>
                            </div>
                        </div>
                        
                        @error('custom_days')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" id="time_section">
                        <!-- Giờ bắt đầu -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Giờ bắt đầu</label>
                            <input type="time" id="start_time" name="start_time" value="{{ old('start_time', '08:00') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('start_time')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Giờ kết thúc -->
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">Giờ kết thúc</label>
                            <input type="time" id="end_time" name="end_time" value="{{ old('end_time', '11:00') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('end_time')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('tutor.availability.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                            Hủy
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Tạo Lịch Rảnh
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function selectPattern(pattern) {
        // Đặt giá trị cho radio button
        document.querySelectorAll('input[name="pattern"]').forEach(input => {
            input.checked = input.value === pattern;
        });
        
        // Hiển thị hoặc ẩn check icon
        document.querySelectorAll('svg.absolute').forEach(svg => {
            svg.classList.add('hidden');
        });
        
        const selectedPattern = document.querySelector(`#${pattern}`);
        if (selectedPattern) {
            const svg = selectedPattern.parentElement.querySelector('svg');
            if (svg) {
                svg.classList.remove('hidden');
            }
            
            // Hiển thị hoặc ẩn phần tùy chỉnh ngày
            const customDaysSection = document.getElementById('custom_days_section');
            if (pattern === 'custom') {
                customDaysSection.classList.remove('hidden');
            } else {
                customDaysSection.classList.add('hidden');
            }
            
            // Thiết lập giờ bắt đầu và kết thúc dựa trên mẫu đã chọn
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            switch(pattern) {
                case 'weekday_mornings':
                    startTimeInput.value = '08:00';
                    endTimeInput.value = '11:00';
                    break;
                case 'weekday_evenings':
                    startTimeInput.value = '18:00';
                    endTimeInput.value = '21:00';
                    break;
                case 'weekends':
                    startTimeInput.value = '09:00';
                    endTimeInput.value = '17:00';
                    break;
            }
        }
    }
    
    // Khởi tạo form
    document.addEventListener('DOMContentLoaded', function() {
        const pattern = document.querySelector('input[name="pattern"]:checked');
        if (pattern) {
            selectPattern(pattern.value);
        }
    });
</script>
@endsection 