@extends('layouts.tutor')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg transition-all duration-300 hover:shadow-lg">
        <div class="px-4 py-5 sm:px-6 flex flex-col md:flex-row justify-between items-start md:items-center bg-gradient-to-r from-indigo-50 to-white">
            <div>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Quản Lý Lịch Rảnh
                </h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-600">
                    Thiết lập thời gian bạn có thể dạy học để học sinh dễ dàng đặt lịch
                </p>
            </div>

            <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('tutor.availability.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Thêm Lịch Rảnh
                </a>
          
            </div>
        </div>

        <!-- Tab menu -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button id="tab-list" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-all duration-300" onclick="switchTab('list', 'calendar')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Danh sách
                </button>
                <button id="tab-calendar" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-all duration-300" onclick="switchTab('calendar', 'list')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lịch tuần
                </button>
            </nav>
        </div>

        <!-- Danh sách lịch rảnh (tab mặc định) -->
        <div id="content-list" class="block">
            @if($availabilities->isEmpty())
                <div class="text-center py-12 bg-gray-50 rounded-lg m-6">
                    <div class="animate-pulse">
                        <svg class="mx-auto h-20 w-20 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Không có lịch rảnh</h3>
                    <p class="mt-2 text-base text-gray-600 max-w-md mx-auto">
                        Bắt đầu bằng cách thêm lịch rảnh cho các khung giờ bạn có thể dạy học. Điều này giúp học sinh dễ dàng tìm thấy và đặt lịch với bạn.
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('tutor.availability.create') }}" class="inline-flex items-center px-5 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Thêm Lịch Rảnh
                        </a>
                        <a href="{{ route('tutor.availability.quick') }}" class="inline-flex items-center px-5 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thêm Nhanh
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Danh sách lịch rảnh của bạn
                        </h3>
                        <span class="text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full text-sm">
                            {{ $availabilities->count() }} khung giờ
                        </span>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 shadow-sm rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ngày
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($availabilities as $availability)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-800 font-semibold">
                                                {{ substr($availability->dayName, 0, 2) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $availability->dayName }}
                                                </div>
                                                @if($availability->date)
                                                    <div class="text-sm text-gray-500">
                                                        Ngày: {{ $availability->date->format('d/m/Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $availability->timeRange }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(isset($availability->status) && $availability->status == 'inactive')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Không khả dụng
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Khả dụng
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('tutor.availability.edit', $availability->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md inline-flex items-center mr-2 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Sửa
                                        </a>
                                        <form action="{{ route('tutor.availability.destroy', $availability->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md inline-flex items-center transition-colors" onclick="return confirm('Bạn có chắc muốn xóa lịch rảnh này?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Lịch tuần -->
        <div id="content-calendar" class="hidden p-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                <div class="flex justify-between items-center px-4 py-2 bg-indigo-50">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Lịch tuần
                    </h3>
                    <span class="text-sm text-gray-600">Tuần hiện tại</span>
                </div>
                
                <div class="grid grid-cols-7 gap-px border-b border-gray-200 bg-gray-100">
                    @foreach($daysOfWeek as $index => $day)
                        <div class="text-center py-3 {{ $index == date('w') ? 'bg-indigo-50 font-semibold text-indigo-600' : 'bg-white' }}">
                            <div class="font-medium">{{ $day }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                @php
                                    $date = now()->startOfWeek()->addDays($index);
                                    echo $date->format('d/m');
                                @endphp
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    @foreach($daysOfWeek as $index => $day)
                        <div class="bg-white p-2 min-h-[20rem] overflow-y-auto relative {{ $index == date('w') ? 'bg-indigo-50' : '' }}">
                            @php
                                $dayAvailabilities = $availabilities->where('day_of_week', $index);
                            @endphp
                            
                            @if($dayAvailabilities->count() > 0)
                                @foreach($dayAvailabilities as $availability)
                                    <div class="mb-2 p-2 text-sm rounded-md cursor-pointer transition-all duration-300 hover:shadow-md {{ $availability->status == 'inactive' ? 'bg-red-100 border border-red-200 text-red-800' : 'bg-green-100 border border-green-200 text-green-800' }}"
                                         onclick="window.location.href='{{ route('tutor.availability.edit', $availability->id) }}'">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $availability->start_time->format('H:i') }} - {{ $availability->end_time->format('H:i') }}
                                        </div>
                                        <div class="text-xs mt-1 flex justify-between items-center">
                                            <span>{{ round((strtotime($availability->end_time->format('H:i')) - strtotime($availability->start_time->format('H:i'))) / 3600, 1) }} giờ</span>
                                            <span>
                                                @if($availability->status == 'inactive')
                                                    <span class="inline-flex items-center text-xs">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Bận
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center text-xs">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Rảnh
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="h-full flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-gray-400 text-xs text-center">Chưa có<br>lịch rảnh</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex space-x-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-100 border border-green-200 rounded-full mr-1"></div>
                                <span class="text-xs text-gray-600">Có thể dạy</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-100 border border-red-200 rounded-full mr-1"></div>
                                <span class="text-xs text-gray-600">Không khả dụng</span>
                            </div>
                        </div>
                        <a href="{{ route('tutor.availability.create') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Thêm lịch rảnh mới
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(show, hide) {
            document.getElementById('content-' + show).classList.remove('hidden');
            document.getElementById('content-' + show).classList.add('block');
            document.getElementById('content-' + hide).classList.remove('block');
            document.getElementById('content-' + hide).classList.add('hidden');
            
            document.getElementById('tab-' + show).classList.add('border-indigo-500', 'text-indigo-600');
            document.getElementById('tab-' + show).classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            
            document.getElementById('tab-' + hide).classList.remove('border-indigo-500', 'text-indigo-600');
            document.getElementById('tab-' + hide).classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        }
    </script>
@endsection 