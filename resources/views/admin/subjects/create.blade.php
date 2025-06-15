@extends('layouts.admin')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Thêm Môn Học Mới</h2>
            <a href="{{ route('admin.subjects.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Quay Lại
            </a>
        </div>

        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <form action="{{ route('admin.subjects.store') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Tên Môn Học
                        </label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="category" class="block text-sm font-medium text-gray-700">
                            Danh Mục
                        </label>
                        <div class="mt-1">
                            <select id="category" name="category" required
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">Chọn danh mục</option>
                                <option value="Khoa học xã hội" {{ old('category') == 'Khoa học xã hội' ? 'selected' : '' }}>Khoa học xã hội</option>
                                <option value="Khoa học tự nhiên" {{ old('category') == 'Ngữ văn' ? 'selected' : '' }}>Ngữ văn</option>
                                <option value="Ngoại ngữ" {{ old('category') == 'Ngoại ngữ' ? 'selected' : '' }}>Ngoại ngữ</option>
                            </select>
                        </div>
                        @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Mô Tả
                        </label>
                        <div class="mt-1">
                            <textarea id="description" name="description" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input hidden id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Thêm Môn Học
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 