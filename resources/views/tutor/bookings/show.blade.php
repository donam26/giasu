@extends('layouts.tutor')

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Chi Tiết Buổi Học
        </h3>
    </div>
    <div class="border-t border-gray-200">
        <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Học sinh
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $booking->student->name }}
                </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Môn học
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $booking->subject->name }}
                </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Thời gian bắt đầu
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $booking->start_time->format('d/m/Y H:i') }}
                </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Thời gian kết thúc
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $booking->end_time->format('d/m/Y H:i') }}
                </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Giá tiền mỗi giờ
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ number_format($booking->price_per_hour, 0, ',', '.') }}đ
                </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Tổng tiền
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ number_format($booking->total_amount, 0, ',', '.') }}đ
                </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Trạng thái
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status == 'confirmed') bg-green-100 text-green-800
                        @elseif($booking->status == 'completed') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800 @endif">
                        @if($booking->status == 'pending') Chờ xác nhận
                        @elseif($booking->status == 'confirmed') Đã xác nhận
                        @elseif($booking->status == 'completed') Hoàn thành
                        @else Đã hủy @endif
                    </span>
                </dd>
            </div>
            @if($booking->notes)
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Ghi chú
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $booking->notes }}
                </dd>
            </div>
            @endif
        </dl>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <a href="{{ route('tutor.bookings.index') }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Quay lại
        </a>
        @if($booking->status == 'pending')
            <form action="{{ route('tutor.bookings.update-status', $booking) }}" method="POST" class="inline-block ml-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Xác nhận
                </button>
            </form>
        @endif
        @if($booking->status == 'confirmed')
            <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="openCompletionModal()">
                Hoàn thành
            </button>
            
            <!-- Modal hoàn thành buổi học -->
            <div id="completionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Hoàn thành buổi học</h3>
                    <form action="{{ route('tutor.bookings.update-status', $booking) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        
                        <div class="mb-4">
                            <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Ghi chú hoàn thành buổi học
                            </label>
                            <textarea name="completion_notes" id="completion_notes" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Nhập ghi chú về buổi học" required></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-5">
                            <button type="button" onclick="closeCompletionModal()" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Hủy
                            </button>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Xác nhận hoàn thành
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        @if($booking->status == 'pending' || $booking->status == 'confirmed')
            <form action="{{ route('tutor.bookings.update-status', $booking) }}" method="POST" class="inline-block ml-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn hủy buổi học này?')" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Hủy
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCompletionModal() {
        document.getElementById('completionModal').classList.remove('hidden');
    }
    
    function closeCompletionModal() {
        document.getElementById('completionModal').classList.add('hidden');
    }
</script>
@endpush 