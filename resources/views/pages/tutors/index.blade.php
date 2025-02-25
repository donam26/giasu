<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('tutors.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Subject Filter -->
                            <div>
                                <label for="subject_id" class="block text-sm font-medium text-gray-700">Môn Học</label>
                                <select id="subject_id" name="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Tất cả môn học</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Class Level Filter -->
                            <div>
                                <label for="class_level_id" class="block text-sm font-medium text-gray-700">Cấp Học</label>
                                <select id="class_level_id" name="class_level_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Tất cả cấp học</option>
                                    @foreach($classLevels as $level)
                                        <option value="{{ $level->id }}" {{ request('class_level_id') == $level->id ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div>
                                <label for="price_range" class="block text-sm font-medium text-gray-700">Học Phí (VNĐ/giờ)</label>
                                <div class="mt-1 grid grid-cols-2 gap-2">
                                    <input type="number" name="min_price" id="min_price" placeholder="Tối thiểu" value="{{ request('min_price') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="number" name="max_price" id="max_price" placeholder="Tối đa" value="{{ request('max_price') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Rating Filter -->
                            <div>
                                <label for="rating" class="block text-sm font-medium text-gray-700">Đánh Giá Tối Thiểu</label>
                                <select id="rating" name="rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Tất cả đánh giá</option>
                                    @foreach(range(5, 1) as $rating)
                                        <option value="{{ $rating }}" {{ request('rating') == $rating ? 'selected' : '' }}>
                                            {{ $rating }} sao trở lên
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Online Teaching -->
                            <div class="flex items-center">
                                <input type="checkbox" id="online_only" name="online_only" value="1" {{ request('online_only') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="online_only" class="ml-2 block text-sm text-gray-900">
                                    Chỉ hiển thị gia sư dạy online
                                </label>
                            </div>

                            <!-- Sort -->
                            <div>
                                <label for="sort_by" class="block text-sm font-medium text-gray-700">Sắp Xếp Theo</label>
                                <select id="sort_by" name="sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Đánh giá</option>
                                    <option value="hourly_rate" {{ request('sort_by') == 'hourly_rate' ? 'selected' : '' }}>Học phí</option>
                                    <option value="total_teaching_hours" {{ request('sort_by') == 'total_teaching_hours' ? 'selected' : '' }}>Kinh nghiệm</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Lọc Kết Quả
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tutors Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tutors as $tutor)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <img class="h-16 w-16 rounded-full object-cover" src="{{ $tutor->avatar ? Storage::url($tutor->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($tutor->user->name) }}" alt="{{ $tutor->user->name }}">
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <a href="{{ route('tutors.show', $tutor) }}" class="hover:text-indigo-600">
                                            {{ $tutor->user->name }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ $tutor->education_level }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="flex items-center">
                                    <span class="text-yellow-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $tutor->rating)
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor
                                    </span>
                                    <span class="ml-1 text-sm text-gray-500">{{ number_format($tutor->rating, 1) }}</span>
                                </div>

                                <div class="mt-2 text-sm text-gray-500">
                                    <p>{{ Str::limit($tutor->bio, 100) }}</p>
                                </div>

                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-900">Môn dạy:</h4>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($tutor->subjects as $subject)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ $subject->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-lg font-medium text-gray-900">
                                        {{ number_format($tutor->hourly_rate) }} VNĐ/giờ
                                    </span>
                                    <a href="{{ route('tutors.show', $tutor) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Xem Chi Tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tutors->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout> 