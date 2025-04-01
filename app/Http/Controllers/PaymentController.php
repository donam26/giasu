<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $vnpayService;

    public function __construct(VNPayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    public function createPayment(Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->student_id !== Auth::id()) {
            abort(403);
        }

        // Kiểm tra trạng thái booking
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Buổi học này không thể thanh toán');
        }

        try {
            $result = $this->vnpayService->createPayment($booking);
            
            return redirect($result['redirect_url']);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage());
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            $result = $this->vnpayService->processCallback($request);
            
            // Lấy thông tin user_id từ query parameter hoặc kết quả callback
            $userId = $request->input('user_id') ?? ($result['user_id'] ?? null);
            
            // Nếu có user_id nhưng không đăng nhập, thử đăng nhập lại
            if ($userId && !Auth::check()) {
                try {
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        Auth::login($user);
                        // Ghi log đăng nhập lại
                        Log::info('Auto login user after payment', ['user_id' => $userId]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error auto login user', ['error' => $e->getMessage()]);
                }
            }
            
            if ($result['success']) {
                // Lưu thông báo thành công vào session
                session()->flash('success', $result['message']);
                
                // Kiểm tra xem người dùng đã đăng nhập chưa (sau khi thử đăng nhập lại ở trên)
                if (Auth::check()) {
                    return redirect()->route('student.bookings.index');
                } else {
                    // Nếu có user_id, chuyển hướng về trang login với redirect sau khi đăng nhập
                    if ($userId) {
                        return redirect()->route('login')
                            ->with('success', $result['message'])
                            ->with('redirect', route('student.bookings.index'));
                    }
                    
                    // Không có user_id, chuyển hướng về trang chủ
                    return redirect()->route('home')
                        ->with('success', $result['message']);
                }
            }

            // Chi tiết lỗi thanh toán
            $errorMessage = $result['message'];
            $errorCode = $result['error_code'] ?? '';
            
            if ($errorCode == '24') {
                // Người dùng tự hủy giao dịch
                $message = 'Bạn đã hủy giao dịch thanh toán. Buổi học của bạn chưa được xác nhận.';
            } else {
                // Các lỗi khác
                $message = 'Thanh toán thất bại: ' . $errorMessage . ' Vui lòng thử lại hoặc chọn phương thức thanh toán khác.';
            }

            // Lưu thông báo lỗi vào session
            session()->flash('error', $message);
            
            // Kiểm tra xem người dùng đã đăng nhập chưa
            if (Auth::check()) {
                return redirect()->route('student.bookings.index');
            } else {
                // Nếu có user_id, chuyển hướng về trang login với redirect sau khi đăng nhập
                if ($userId) {
                    return redirect()->route('login')
                        ->with('error', $message)
                        ->with('redirect', route('student.bookings.index'));
                }
                
                // Không có user_id, chuyển hướng về trang chủ
                return redirect()->route('home')
                    ->with('error', $message);
            }
        } catch (\Exception $e) {
            // Lưu thông báo lỗi vào session và ghi log
            Log::error('Payment callback error', [
            // Lưu thông báo lỗi vào session
            session()->flash('error', 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage());
            
            // Luôn chuyển hướng về trang chủ trong trường hợp lỗi
            return redirect()->route('home')
                ->with('error', 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng liên hệ hỗ trợ.');
        }
    }
} 