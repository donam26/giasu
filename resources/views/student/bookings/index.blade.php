@extends('layouts.app')

@section('content')
<style>
    .bgyl {
        background-color: #ffc107;
    }
</style>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 sm:px-0 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Danh Sách Buổi Học</h1>
            <div class="space-x-2">
                <a href="{{ route('student.bookings.tutors') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Gia sư của tôi
                </a>
                <a href="{{ route('student.reschedules.index') }}" class="bgyl inline-block px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                    Yêu cầu đổi lịch
                    @php
                        $pendingCount = Auth::user()->bookings()
                            ->where('reschedule_requested', true)
                            ->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gia sư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Môn học
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tổng tiền
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Thao tác</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($bookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($booking->tutor->avatar)
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ Storage::url($booking->tutor->avatar) }}" alt="">
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $booking->tutor->user->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $booking->tutor->education_level }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $booking->subject->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $booking->start_time->format('d/m/Y H:i') }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->end_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($booking->total_amount, 0, ',', '.') }}đ
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('student.bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Chi tiết
                                        </a>
                                        @if($booking->status === 'pending')
                                            <form action="{{ route('student.bookings.rebook', $booking) }}" method="POST" class="inline mr-2">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900" onclick="return confirm('Bạn có chắc chắn muốn đặt lại buổi học này? Lịch cũ sẽ bị hủy.')">
                                                    Đặt lại
                                                </button>
                                            </form>
                                        @endif
                                        @if(in_array($booking->status, ['pending', 'confirmed']))
                                            <form action="{{ route('student.bookings.cancel', $booking) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc chắn muốn hủy buổi học này?')">
                                                    Hủy
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        Bạn chưa có buổi học nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>

    <!-- Thông báo nhắc nhở đánh giá -->
    @php
        $completedUnreviewedBookings = $bookings->where('status', 'completed')
            ->filter(function($booking) {
                return !App\Models\Review::where('student_id', auth()->id())
                    ->where('booking_id', $booking->id)
                    ->exists();
            });
    @endphp
    
    @if($completedUnreviewedBookings->count() > 0)
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Bạn có {{ $completedUnreviewedBookings->count() }} buổi học đã hoàn thành chưa được đánh giá. 
                        <a href="#unreviewed-bookings" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Xem ngay
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($completedUnreviewedBookings->count() > 0)
        <div id="unreviewed-bookings" class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Buổi học cần đánh giá</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gia sư</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Môn học</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Thao tác</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($completedUnreviewedBookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ $booking->tutor->avatar ? Storage::url($booking->tutor->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($booking->tutor->user->name) }}" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $booking->tutor->user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->subject->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->start_time->format('H:i d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->end_time->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Cần đánh giá
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('student.bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Xem</a>
                                    <a href="{{ route('student.tutors.review', ['tutor' => $booking->tutor, 'booking' => $booking->id]) }}" class="text-yellow-600 hover:text-yellow-900">Đánh giá</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection 