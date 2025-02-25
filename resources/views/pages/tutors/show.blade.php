<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Thông tin cơ bản -->
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center">
                                <img class="h-24 w-24 rounded-full object-cover" src="{{ $tutor->avatar ? Storage::url($tutor->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($tutor->user->name) }}" alt="{{ $tutor->user->name }}">
                                <div class="ml-4">
                                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                        {{ $tutor->user->name }}
                                    </h2>
                                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                            </svg>
                                            {{ $tutor->education_level }}
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                                            </svg>
                                            {{ $tutor->university }}
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $tutor->major }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 flex lg:mt-0 lg:ml-4">
                            <span class="hidden sm:block">
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                    Liên hệ
                                </button>
                            </span>

                            <span class="ml-3">
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                    Đặt lịch học
                                </button>
                            </span>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết -->
                    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Giới thiệu</h3>
                            <div class="mt-2 text-sm text-gray-500">
                                {{ $tutor->bio }}
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Kinh nghiệm giảng dạy</h3>
                                <div class="mt-2 text-sm text-gray-500">
                                    {{ $tutor->teaching_experience }}
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Môn học</h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($tutor->subjects as $subject)
                                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                            {{ $subject->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Cấp học</h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($tutor->classLevels as $level)
                                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            {{ $level->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Thông tin học phí</h3>
                                <dl class="mt-5 grid grid-cols-1 gap-5">
                                    <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Học phí cơ bản
                                        </dt>
                                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                            {{ number_format($tutor->hourly_rate) }} VNĐ/giờ
                                        </dd>
                                    </div>

                                    @if($tutor->can_teach_online)
                                        <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Hình thức dạy
                                            </dt>
                                            <dd class="mt-1">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Trực tiếp
                                                </span>
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Online
                                                </span>
                                            </dd>
                                        </div>
                                    @endif

                                    <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Khu vực dạy
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            @if($tutor->teaching_locations)
                                                @foreach($tutor->teaching_locations as $location)
                                                    <div class="mb-1">{{ $location }}</div>
                                                @endforeach
                                            @else
                                                <div class="text-gray-500">Chưa cập nhật</div>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            @if($tutor->certification_files)
                                <div class="mt-6">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Chứng chỉ</h3>
                                    <ul role="list" class="mt-2 grid grid-cols-2 gap-4">
                                        @foreach($tutor->certification_files as $file)
                                            <li class="relative">
                                                <div class="group block w-full aspect-w-10 aspect-h-7 rounded-lg bg-gray-100 overflow-hidden">
                                                    <img src="{{ Storage::url($file) }}" alt="Chứng chỉ" class="object-cover pointer-events-none">
                                                    <button type="button" class="absolute inset-0 focus:outline-none">
                                                        <span class="sr-only">Xem chứng chỉ</span>
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 