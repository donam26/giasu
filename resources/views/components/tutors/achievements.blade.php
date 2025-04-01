@props(['tutor', 'certificates' => null])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Chứng chỉ & Thành tích</h3>
        </div>
        
        @if($certificates && count($certificates) > 0)
            <div class="space-y-6">
                @foreach($certificates as $certificate)
                    <div class="bg-gray-50 rounded-lg p-4 flex">
                        <div class="flex-shrink-0">
                            @if($certificate->image)
                                <img src="{{ Storage::url($certificate->image) }}" alt="{{ $certificate->title }}" class="h-16 w-16 object-cover rounded-md border border-gray-200 shadow-sm">
                            @else
                                <div class="h-16 w-16 bg-indigo-100 rounded-md border border-indigo-200 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-medium text-gray-900">{{ $certificate->title }}</h4>
                            <p class="mt-1 text-sm text-gray-600">{{ $certificate->issuer }} • {{ $certificate->issued_date->format('d/m/Y') }}</p>
                            @if($certificate->description)
                                <p class="mt-2 text-sm text-gray-500">{{ $certificate->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 p-5 rounded-lg">
                <div class="text-center py-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có chứng chỉ</h3>
                    <p class="mt-1 text-sm text-gray-500">Gia sư chưa thêm chứng chỉ hoặc thành tích.</p>
                </div>
            </div>
        @endif

        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Thành tích học tập & Hoạt động nổi bật</h4>
            
            <div class="bg-gray-50 p-5 rounded-lg">
                @if($tutor->achievements)
                    <div class="prose max-w-none">
                        {!! nl2br(e($tutor->achievements)) !!}
                    </div>
                @else
                    <ul class="space-y-4">
                        @if($tutor->high_school)
                            <li class="flex items-start">
                                <svg class="h-6 w-6 mr-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Tốt nghiệp THPT tại {{ $tutor->high_school }}</span>
                            </li>
                        @endif
                        
                        @if($tutor->achievements_json)
                            @foreach(json_decode($tutor->achievements_json, true) as $achievement)
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 mr-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ $achievement }}</span>
                                </li>
                            @endforeach
                        @else
                            <li class="text-gray-500 italic">Gia sư chưa cập nhật thành tích và hoạt động.</li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div> 