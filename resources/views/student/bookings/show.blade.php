@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Chi tiết buổi học</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Thông tin chi tiết về buổi học của bạn.
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow sm:rounded-lg">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Gia sư</dt>
                            <dd class="mt-1">
                                <div class="flex items-center">
                                    @if($booking->tutor->avatar)
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ Storage::url($booking->tutor->avatar) }}" alt="">
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $booking->tutor->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->tutor->education_level }}</div>
                                    </div>
                                </div>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Môn học</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $booking->subject->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                            <dd class="mt-1">
                                @switch($booking->status)
                                    @case('pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Chờ xác nhận
                                        </span>
                                        @break
                                    @case('confirmed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Đã xác nhận
                                        </span>
                                        @break
                                    @case('completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Đã hoàn thành
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Đã hủy
                                        </span>
                                        @break
                                @endswitch
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Thời gian bắt đầu</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $booking->start_time->format('d/m/Y H:i') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Thời gian kết thúc</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $booking->end_time->format('H:i') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Giá mỗi giờ</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($booking->price_per_hour, 0, ',', '.') }}đ</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tổng tiền</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</dd>
                        </div>

                        @if($booking->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Ghi chú</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="{{ route('student.bookings.index') }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                        Quay lại
                    </a>
                    @if($booking->status === 'pending')
                        <div class="mt-6 flex justify-end space-x-3">
                            <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="document.getElementById('cancel-modal').classList.remove('hidden')">
                                Hủy buổi học
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($booking->status === 'completed' && !App\Models\Review::where('booking_id', $booking->id)->exists())
    <div class="mt-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Đánh giá gia sư
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Chia sẻ đánh giá của bạn về buổi học với gia sư {{ $booking->tutor->user->name }}</p>
                </div>
                <form action="{{ route('student.tutors.review.store', $booking->tutor) }}" method="POST" class="mt-5">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá</label>
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex items-center mr-4 cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" {{ old('rating') == $i ? 'checked' : '' }}>
                                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 peer-checked:border-yellow-400 peer-checked:bg-yellow-50">
                                        <svg class="h-6 w-6 {{ old('rating') >= $i ? 'text-yellow-400' : 'text-gray-300' }} rating-star" data-rating="{{ $i }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <span class="ml-2">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        @error('rating')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-6">
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Nhận xét của bạn</label>
                        <textarea id="comment" name="comment" rows="4" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Hãy chia sẻ trải nghiệm của bạn với gia sư này...">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input id="is_anonymous" name="is_anonymous" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('is_anonymous') ? 'checked' : '' }}>
                            <label for="is_anonymous" class="ml-2 block text-sm text-gray-700">
                                Đánh giá ẩn danh
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Tên của bạn sẽ không hiển thị công khai nếu bạn chọn đánh giá ẩn danh</p>
                    </div>
                    
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Gửi đánh giá
                    </button>
                </form>
            </div>
        </div>
    </div>
@elseif($booking->status === 'completed' && App\Models\Review::where('booking_id', $booking->id)->exists())
    <div class="mt-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-green-50 border border-green-100 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        Bạn đã đánh giá gia sư này
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Cảm ơn bạn đã chia sẻ đánh giá về gia sư. Đánh giá của bạn sẽ giúp những học viên khác có thêm thông tin khi lựa chọn gia sư.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($booking->status === 'cancelled')
    <div class="mt-4 bg-red-50 border border-red-100 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Buổi học đã bị hủy
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    @if ($booking->cancelled_by === 'student')
                        <p>Bạn đã hủy buổi học này.</p>
                    @else
                        <p>Gia sư đã hủy buổi học này.</p>
                    @endif
                    
                    @if ($booking->cancelled_reason)
                        <p class="mt-1"><strong>Lý do:</strong> {{ $booking->cancelled_reason }}</p>
                    @endif
                    
                    @if ($booking->payments()->where('status', 'completed')->exists())
                        @if ($booking->refund_percentage > 0)
                            <p class="mt-1 font-medium text-green-800">
                                @if ($booking->refund_percentage == 100)
                                    Bạn đã được hoàn lại 100% học phí ({{ number_format($booking->total_amount, 0, ',', '.') }}đ).
                                @elseif ($booking->refund_percentage == 50)
                                    @php $refundAmount = $booking->total_amount * 0.5; @endphp
                                    Bạn đã được hoàn lại 50% học phí ({{ number_format($refundAmount, 0, ',', '.') }}đ).
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Số tiền hoàn lại sẽ được chuyển vào phương thức thanh toán ban đầu của bạn trong vòng 3-5 ngày làm việc.
                            </p>
                        @else
                            <p class="mt-1">Bạn không được hoàn tiền do hủy buổi học quá gần thời gian bắt đầu.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modal hủy buổi học -->
<div id="cancel-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('student.bookings.cancel', $booking) }}" method="POST">
                @csrf
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Xác nhận hủy buổi học
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Bạn chắc chắn muốn hủy buổi học này? Hành động này không thể hoàn tác.
                                </p>
                                
                                <div class="mt-4">
                                    <label for="cancelled_reason" class="block text-sm font-medium text-gray-700">Lý do hủy</label>
                                    <textarea id="cancelled_reason" name="cancelled_reason" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Vui lòng cho biết lý do bạn hủy buổi học này"></textarea>
                                </div>
                                
                                @php
                                $hoursUntilBooking = now()->diffInHours($booking->start_time, false);
                                $refundInfo = 'Bạn sẽ không được hoàn tiền do hủy quá gần thời gian học.';
                                
                                if ($hoursUntilBooking >= 24) {
                                    $refundInfo = 'Bạn sẽ được hoàn lại 100% học phí (' . number_format($booking->total_amount, 0, ',', '.') . 'đ).';
                                } elseif ($hoursUntilBooking >= 12) {
                                    $refundAmount = $booking->total_amount * 0.5;
                                    $refundInfo = 'Bạn sẽ được hoàn lại 50% học phí (' . number_format($refundAmount, 0, ',', '.') . 'đ).';
                                }
                                @endphp
                                
                                <div class="mt-4 bg-yellow-50 p-3 rounded-md">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Chính sách hoàn tiền:</strong>
                                        <ul class="mt-1 list-disc pl-5 text-xs">
                                            <li>Hủy trước 24 giờ: hoàn tiền 100%</li>
                                            <li>Hủy trong vòng 12-24 giờ: hoàn tiền 50%</li>
                                            <li>Hủy trong vòng dưới 12 giờ: không hoàn tiền</li>
                                        </ul>
                                    </p>
                                    <p class="mt-2 text-sm text-yellow-800">
                                        <strong>Trong trường hợp của bạn:</strong> {{ $refundInfo }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Xác nhận hủy
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('cancel-modal').classList.add('hidden')">
                        Đóng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý hiệu ứng đánh giá sao
        const ratingStars = document.querySelectorAll('.rating-star');
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                
                // Cập nhật màu sắc của các sao
                ratingStars.forEach(s => {
                    const starRating = parseInt(s.dataset.rating);
                    if (starRating <= rating) {
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-400');
                    } else {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300');
                    }
                });
                
                // Chọn input radio tương ứng
                ratingInputs.forEach(input => {
                    if (parseInt(input.value) === rating) {
                        input.checked = true;
                    }
                });
            });
            
            // Hiệu ứng hover
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                
                ratingStars.forEach(s => {
                    const starRating = parseInt(s.dataset.rating);
                    if (starRating <= rating) {
                        s.classList.add('text-yellow-300');
                    }
                });
            });
            
            star.addEventListener('mouseleave', function() {
                ratingStars.forEach(s => {
                    s.classList.remove('text-yellow-300');
                });
            });
        });
    });
</script>
@endpush
@endsection 