@extends('layouts.tutor')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Tạo Nhanh Nhiều Lịch Rảnh
            </h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Thiết lập nhanh nhiều khung giờ dạy học cùng lúc
            </p>
        </div>

        <form method="POST" action="{{ route('tutor.schedule.quick-store') }}" id="quick-create-form">
            @csrf

            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Ngày cụ thể (tùy chọn)
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <input type="date" name="date" id="date" value="{{ old('date') }}"
                                class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Để trống nếu lịch này lặp lại hàng tuần. Nếu chọn ngày cụ thể, lịch này sẽ chỉ hiển thị cho ngày đó.</p>
                            @error('date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </dd>
                    </div>

                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Chọn ngày trong tuần <span class="text-red-500">*</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($daysOfWeek as $value => $day)
                                    <div class="flex items-center">
                                        <input id="day_{{ $value }}" name="days[]" type="checkbox" value="{{ $value }}" 
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="day_{{ $value }}" class="ml-2 block text-sm text-gray-900">
                                            {{ $day }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('days')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </dd>
                    </div>

                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Trạng thái
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input id="status-available" name="status" type="radio" value="available" {{ old('status', 'available') === 'available' ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="status-available" class="ml-2 block text-sm text-gray-700">
                                        Khả dụng (có thể dạy)
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="status-unavailable" name="status" type="radio" value="unavailable" {{ old('status') === 'unavailable' ? 'checked' : '' }}
                                        class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300">
                                    <label for="status-unavailable" class="ml-2 block text-sm text-gray-700">
                                        Không khả dụng (không thể dạy)
                                    </label>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Chọn "Không khả dụng" nếu bạn muốn đánh dấu thời gian này là không thể dạy học.</p>
                        </dd>
                    </div>

                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Lặp lại
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <input id="is_recurring" name="is_recurring" type="checkbox" value="1" {{ old('is_recurring', true) ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <label for="is_recurring" class="ml-2 block text-sm text-gray-700">
                                    Lặp lại hàng tuần
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Lịch này sẽ được lặp lại hàng tuần. Nếu bạn đã chọn ngày cụ thể, tùy chọn này sẽ không có tác dụng.</p>
                        </dd>
                    </div>

                    <div class="bg-white px-4 py-5 sm:px-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Khung giờ rảnh</h3>
                            <p class="mt-1 text-sm text-gray-500">Thêm các khung giờ bạn có thể dạy học trong những ngày đã chọn</p>
                        </div>

                        <div id="time-slots-container">
                            <div class="time-slot mb-4 border-b pb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Giờ bắt đầu</label>
                                        <input type="time" name="timeSlots[0][start]" required
                                            class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Giờ kết thúc</label>
                                        <input type="time" name="timeSlots[0][end]" required
                                            class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <button type="button" class="remove-time-slot mt-2 text-sm text-red-600 hidden">Xóa khung giờ này</button>
                            </div>
                        </div>

                        <button type="button" id="add-time-slot" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-0.5 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Thêm khung giờ
                        </button>

                        @error('timeSlots')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @error('general')
                        <div class="bg-red-50 px-4 py-5 sm:px-6">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror

                    <div class="bg-white px-4 py-5 sm:px-6 flex justify-end space-x-3">
                        <a href="{{ route('tutor.schedule.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Hủy
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Lưu Lịch Rảnh
                        </button>
                    </div>
                </dl>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timeSlotIndex = 0;
            const container = document.getElementById('time-slots-container');
            const addButton = document.getElementById('add-time-slot');

            addButton.addEventListener('click', function() {
                timeSlotIndex++;
                
                const timeSlotDiv = document.createElement('div');
                timeSlotDiv.className = 'time-slot mb-4 border-b pb-4';
                timeSlotDiv.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Giờ bắt đầu</label>
                            <input type="time" name="timeSlots[${timeSlotIndex}][start]" required
                                class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Giờ kết thúc</label>
                            <input type="time" name="timeSlots[${timeSlotIndex}][end]" required
                                class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <button type="button" class="remove-time-slot mt-2 text-sm text-red-600">Xóa khung giờ này</button>
                `;
                
                container.appendChild(timeSlotDiv);
                
                // Hiển thị các nút xóa nếu có nhiều hơn một khung giờ
                updateRemoveButtons();
            });
            
            // Xử lý sự kiện xóa khung giờ (được thêm động)
            container.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-time-slot')) {
                    e.target.closest('.time-slot').remove();
                    
                    // Cập nhật trạng thái nút xóa
                    updateRemoveButtons();
                }
            });
            
            // Hàm cập nhật trạng thái nút xóa
            function updateRemoveButtons() {
                const timeSlots = container.querySelectorAll('.time-slot');
                const removeButtons = container.querySelectorAll('.remove-time-slot');
                
                if (timeSlots.length > 1) {
                    removeButtons.forEach(button => {
                        button.classList.remove('hidden');
                    });
                } else {
                    removeButtons.forEach(button => {
                        button.classList.add('hidden');
                    });
                }
            }
            
            // Kiểm tra form trước khi gửi
            document.getElementById('quick-create-form').addEventListener('submit', function(e) {
                const daysChecked = document.querySelectorAll('input[name="days[]"]:checked').length;
                if (daysChecked === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một ngày trong tuần.');
                }
            });
        });
    </script>
@endsection 