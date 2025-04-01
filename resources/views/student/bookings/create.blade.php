@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Đặt lịch học</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Vui lòng điền đầy đủ thông tin để đặt lịch học với gia sư {{ $tutor->user->name }}.
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('student.bookings.store', $tutor) }}" method="POST">
                @csrf
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <!-- Môn học -->
                        <div>
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">
                                Môn học
                            </label>
                            <select id="subject_id" name="subject_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Chọn môn học</option>
                                @foreach($tutor->subjects as $subject)
                                    <option value="{{ $subject->id }}" data-price="{{ $subject->pivot->price_per_hour ?? $tutor->hourly_rate }}">
                                        {{ $subject->name }} - {{ format_vnd($subject->pivot->price_per_hour ?? $tutor->hourly_rate) }}/giờ
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Thời gian bắt đầu -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">
                                Thời gian bắt đầu
                            </label>
                            <input type="datetime-local" name="start_time" id="start_time" 
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                min="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('start_time')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Thời lượng -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700">
                                Thời lượng (giờ)
                            </label>
                            <select id="duration" name="duration" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="1">1 giờ</option>
                                <option value="2">2 giờ</option>
                                <option value="3">3 giờ</option>
                                <option value="4">4 giờ</option>
                            </select>
                            @error('duration')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Ghi chú
                            </label>
                            <div class="mt-1">
                                <textarea id="notes" name="notes" rows="3" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                                    placeholder="Nhập ghi chú cho gia sư (không bắt buộc)"></textarea>
                            </div>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tổng tiền -->
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Tổng tiền:</span>
                                <span id="total_amount" class="text-lg font-semibold text-indigo-600">0đ</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <a href="{{ route('tutors.show', $tutor) }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                            Quay lại
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Đặt lịch và thanh toán
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateTotalAmount() {
        const subjectSelect = document.getElementById('subject_id');
        const durationSelect = document.getElementById('duration');
        const totalAmountSpan = document.getElementById('total_amount');
        
        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        if (selectedOption.value) {
            const pricePerHour = parseFloat(selectedOption.dataset.price);
            const duration = parseInt(durationSelect.value);
            const total = pricePerHour * duration;
            
            // Sử dụng hàm định dạng tiền tệ VN
            fetch(`/api/format-currency?amount=${total}`)
                .then(response => response.json())
                .then(data => {
                    totalAmountSpan.textContent = data.formatted;
                })
                .catch(() => {
                    // Fallback nếu API không hoạt động
                    totalAmountSpan.textContent = new Intl.NumberFormat('vi-VN', { 
                        style: 'currency', 
                        currency: 'VND' 
                    }).format(total);
                });
        } else {
            totalAmountSpan.textContent = '0đ';
        }
    }

    document.getElementById('subject_id').addEventListener('change', updateTotalAmount);
    document.getElementById('duration').addEventListener('change', updateTotalAmount);
</script>
@endpush
@endsection 