<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900">Đăng Ký Làm Gia Sư</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Vui lòng cung cấp thông tin chi tiết để chúng tôi có thể xem xét hồ sơ của bạn.
                    </p>

                    <form action="{{ route('tutors.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <!-- Trình độ học vấn -->
                            <div class="sm:col-span-2">
                                <label for="education_level" class="block text-sm font-medium text-gray-700">
                                    Trình độ học vấn <span class="text-red-500">*</span>
                                </label>
                                <select id="education_level" name="education_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Chọn trình độ</option>
                                    <option value="Sinh viên">Sinh viên</option>
                                    <option value="Cử nhân">Cử nhân</option>
                                    <option value="Thạc sĩ">Thạc sĩ</option>
                                    <option value="Tiến sĩ">Tiến sĩ</option>
                                    <option value="Giảng viên">Giảng viên</option>
                                </select>
                            </div>

                            <!-- Trường đại học -->
                            <div class="sm:col-span-2">
                                <label for="university" class="block text-sm font-medium text-gray-700">
                                    Trường đại học
                                </label>
                                <input type="text" name="university" id="university" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Chuyên ngành -->
                            <div class="sm:col-span-2">
                                <label for="major" class="block text-sm font-medium text-gray-700">
                                    Chuyên ngành
                                </label>
                                <input type="text" name="major" id="major" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Kinh nghiệm giảng dạy -->
                            <div class="sm:col-span-2">
                                <label for="teaching_experience" class="block text-sm font-medium text-gray-700">
                                    Kinh nghiệm giảng dạy <span class="text-red-500">*</span>
                                </label>
                                <textarea id="teaching_experience" name="teaching_experience" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                            <!-- Giới thiệu bản thân -->
                            <div class="sm:col-span-2">
                                <label for="bio" class="block text-sm font-medium text-gray-700">
                                    Giới thiệu bản thân <span class="text-red-500">*</span>
                                </label>
                                <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                            <!-- Ảnh đại diện -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Ảnh đại diện
                                </label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="avatar" id="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>

                            <!-- Chứng chỉ -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Chứng chỉ
                                </label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="certification_files[]" multiple accept="image/*,.pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>

                            <!-- Học phí -->
                            <div>
                                <label for="hourly_rate" class="block text-sm font-medium text-gray-700">
                                    Học phí (VNĐ/giờ) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="hourly_rate" id="hourly_rate" min="0" step="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Khu vực dạy -->
                            <div>
                                <label for="teaching_locations" class="block text-sm font-medium text-gray-700">
                                    Khu vực dạy
                                </label>
                                <select id="teaching_locations" name="teaching_locations[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Quận 1">Quận 1</option>
                                    <option value="Quận 2">Quận 2</option>
                                    <option value="Quận 3">Quận 3</option>
                                    <option value="Quận 4">Quận 4</option>
                                    <option value="Quận 5">Quận 5</option>
                                    <!-- Thêm các quận/huyện khác -->
                                </select>
                            </div>

                            <!-- Dạy online -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="can_teach_online" name="can_teach_online" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="can_teach_online" class="font-medium text-gray-700">Có thể dạy online</label>
                                    <p class="text-gray-500">Tôi có thể dạy trực tuyến qua Zoom, Google Meet, etc.</p>
                                </div>
                            </div>

                            <!-- Môn học -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Môn học <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    @foreach($subjects as $subject)
                                        <div class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="subject_{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="subject_{{ $subject->id }}" class="font-medium text-gray-700">{{ $subject->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Cấp học -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Cấp học <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    @foreach($classLevels as $level)
                                        <div class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="level_{{ $level->id }}" name="class_levels[]" value="{{ $level->id }}" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="level_{{ $level->id }}" class="font-medium text-gray-700">{{ $level->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div class="flex justify-end">
                                <button type="button" onclick="window.history.back()" class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Hủy
                                </button>
                                <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Đăng ký
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 