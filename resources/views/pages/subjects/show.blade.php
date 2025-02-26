<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Subject Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $subject->name }}</h1>
                        <span class="px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-800 rounded-full">
                            {{ $subject->category }}
                        </span>
                    </div>

                    @if($subject->description)
                        <p class="text-gray-600 mb-4">{{ $subject->description }}</p>
                    @endif

                    <div class="flex items-center text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            {{ $subject->tutors->count() }} Gia sư
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tutors List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Gia Sư Dạy {{ $subject->name }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($subject->tutors as $tutor)
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex items-center mb-4">
                                    <img class="h-12 w-12 rounded-full object-cover" 
                                         src="{{ $tutor->avatar ? Storage::url($tutor->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($tutor->user->name) }}" 
                                         alt="{{ $tutor->user->name }}">
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ $tutor->user->name }}
                                        </h3>
                                        <p class="text-sm text-gray-500">{{ $tutor->education_level }}</p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-center mb-2">
                                        <span class="text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $tutor->rating)
                                                    ★
                                                @else
                                                    ☆
                                                @endif
                                            @endfor
                                        </span>
                                        <span class="ml-1 text-sm text-gray-500">
                                            {{ number_format($tutor->rating, 1) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ Str::limit($tutor->bio, 100) }}</p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-medium text-gray-900">
                                        {{ number_format($tutor->hourly_rate) }} VNĐ/giờ
                                    </span>
                                    <a href="{{ route('tutors.show', $tutor) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Xem Chi Tiết
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có gia sư nào dạy môn này</h3>
                                <p class="mt-1 text-sm text-gray-500">Vui lòng quay lại sau.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 