<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\AiRecommendation;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\ClassLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class AIAdvisorController extends Controller
{
    /**
     * Hiển thị trang tư vấn AI
     */
    public function index()
    {
        $conversation = null;
        $recommendations = collect();
        
        return view('pages.ai-advisor.index', compact('conversation', 'recommendations'));
    }

    /**
     * Xử lý tin nhắn chat với AI
     */
    public function chat(Request $request)
    {
        // Debug API key
        Log::info('OpenAI API Key:', [
            'key_exists' => !empty(config('openai.api_key')),
            'key_length' => strlen(config('openai.api_key')),
            'key_value' => substr(config('openai.api_key'), 0, 10) . '...'
        ]);

        // Kiểm tra API key
        if (empty(config('openai.api_key'))) {
            return response()->json([
                'message' => 'Lỗi: Chưa cấu hình OpenAI API key',
                'error' => true
            ], 500);
        }

        try {
            $conversation = AIConversation::firstOrCreate(
                ['id' => $request->session()->get('conversation_id')],
                [
                    'user_id' => Auth::id(),
                    'status' => 'active'
                ]
            );

            $request->session()->put('conversation_id', $conversation->id);

            if ($request->message) {
                $userMessage = new AIMessage([
                    'role' => 'user',
                    'content' => $request->message
                ]);
                $conversation->messages()->save($userMessage);
            }

            $history = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'role' => $message->role,
                        'content' => $message->content
                    ];
                })
                ->toArray();

            $systemMessage = [
                'role' => 'system',
                'content' => 'Bạn là trợ lý AI tư vấn giáo dục. Nhiệm vụ của bạn là hiểu nhu cầu học tập của học sinh và đề xuất gia sư phù hợp. Hãy trả lời ngắn gọn, súc tích và thân thiện. Nếu học sinh muốn xem thông tin chi tiết về gia sư hoặc đặt lịch học, hãy hướng dẫn họ đăng nhập/đăng ký.'
            ];

            if ($request->type === 'summarize') {
                if (!Auth::check()) {
                    return response()->json([
                        'message' => 'Vui lòng đăng nhập để xem đề xuất gia sư chi tiết.',
                        'require_login' => true
                    ]);
                }
                
                $summary = $this->generateSummary($conversation);
                $recommendations = $this->getRecommendations($conversation);

                return response()->json([
                    'summary' => $summary,
                    'recommendations' => $recommendations
                ]);
            }

            try {
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4-turbo-preview',
                    'messages' => array_merge([$systemMessage], $history),
                    'temperature' => 0.7,
                    'max_tokens' => 500
                ]);

                $aiMessage = new AIMessage([
                    'role' => 'assistant',
                    'content' => $response->choices[0]->message->content
                ]);
                $conversation->messages()->save($aiMessage);

                return response()->json([
                    'message' => $aiMessage->content
                ]);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Có lỗi khi gọi OpenAI API: ' . $e->getMessage(),
                    'error' => true
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }

    private function generateSummary($conversation)
    {
        try {
            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'role' => $message->role,
                        'content' => $message->content
                    ];
                })
                ->toArray();

            $systemMessage = [
                'role' => 'system',
                'content' => 'Hãy phân tích cuộc hội thoại và tóm tắt các thông tin quan trọng về nhu cầu học tập của học sinh, bao gồm: môn học cần hỗ trợ, cấp học, mục tiêu học tập, và các yêu cầu đặc biệt nếu có.'
            ];

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4-turbo-preview',
                'messages' => array_merge([$systemMessage], $messages),
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            return $response->choices[0]->message->content;

        } catch (Exception $e) {
            return 'Có lỗi khi tạo tóm tắt: ' . $e->getMessage();
        }
    }

    private function getRecommendations($conversation)
    {
        try {
            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'role' => $message->role,
                        'content' => $message->content
                    ];
                })
                ->toArray();

            $systemMessage = [
                'role' => 'system',
                'content' => 'Hãy phân tích cuộc hội thoại và trích xuất các tiêu chí để tìm gia sư phù hợp, bao gồm: môn học, cấp học, ngân sách, phương pháp giảng dạy mong muốn, v.v.'
            ];

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4-turbo-preview',
                'messages' => array_merge([$systemMessage], $messages),
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            $preferences = json_decode($response->choices[0]->message->content, true);

            // Tìm gia sư phù hợp dựa trên preferences
            $tutors = Tutor::with(['subjects', 'classLevels'])
                ->where('status', 'active')
                ->where('is_verified', true)
                ->get();

            $recommendations = [];
            foreach ($tutors as $tutor) {
                $score = $this->calculateMatchingScore($tutor, $preferences);
                if ($score >= 0.7) { // Chỉ đề xuất gia sư có độ phù hợp từ 70% trở lên
                    $recommendations[] = [
                        'tutor' => $tutor,
                        'matching_score' => $score,
                        'reason' => $this->generateRecommendationReason($tutor, $preferences)
                    ];
                }
            }

            // Sắp xếp theo điểm số phù hợp
            usort($recommendations, function ($a, $b) {
                return $b['matching_score'] <=> $a['matching_score'];
            });

            // Lấy 5 gia sư phù hợp nhất
            $recommendations = array_slice($recommendations, 0, 5);

            // Lưu đề xuất vào database
            foreach ($recommendations as $recommendation) {
                AiRecommendation::create([
                    'conversation_id' => $conversation->id,
                    'tutor_id' => $recommendation['tutor']->id,
                    'matching_score' => $recommendation['matching_score'],
                    'reason' => $recommendation['reason']
                ]);
            }

            return $recommendations;

        } catch (Exception $e) {
            return ['error' => 'Có lỗi khi tạo đề xuất: ' . $e->getMessage()];
        }
    }

    private function calculateMatchingScore($tutor, $preferences)
    {
        $score = 0;
        $weights = [
            'subjects' => 0.3,
            'class_levels' => 0.2,
            'teaching_method' => 0.2,
            'price' => 0.15,
            'experience' => 0.15
        ];

        // Tính điểm cho từng tiêu chí
        // Môn học
        if (isset($preferences['subjects'])) {
            $matchingSubjects = $tutor->subjects->whereIn('name', $preferences['subjects'])->count();
            $score += $weights['subjects'] * ($matchingSubjects / count($preferences['subjects']));
        }

        // Cấp học
        if (isset($preferences['class_levels'])) {
            $matchingLevels = $tutor->classLevels->whereIn('name', $preferences['class_levels'])->count();
            $score += $weights['class_levels'] * ($matchingLevels / count($preferences['class_levels']));
        }

        // Các tiêu chí khác...

        return $score;
    }

    private function generateRecommendationReason($tutor, $preferences)
    {
        $reasons = [];

        // Đánh giá từ học sinh
        if ($tutor->rating >= 4.5) {
            $reasons[] = "Được đánh giá rất cao bởi học sinh ({$tutor->rating}/5.0)";
        }

        // Kinh nghiệm giảng dạy
        if ($tutor->total_teaching_hours > 1000) {
            $reasons[] = "Có nhiều kinh nghiệm giảng dạy (trên {$tutor->total_teaching_hours} giờ)";
        }

        // Môn học phù hợp
        $matchingSubjects = $tutor->subjects->whereIn('name', $preferences['subjects'] ?? [])->pluck('name');
        if ($matchingSubjects->isNotEmpty()) {
            $reasons[] = "Chuyên dạy các môn: " . $matchingSubjects->implode(', ');
        }

        return implode("\n", $reasons);
    }
}
