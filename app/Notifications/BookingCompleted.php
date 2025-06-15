<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bookingData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $bookingData)
    {
        $this->bookingData = $bookingData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Buổi học đã hoàn thành - Đánh giá gia sư')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Buổi học ' . $this->bookingData['subject_name'] . ' với gia sư ' . $this->bookingData['tutor_name'] . ' đã hoàn thành.')
            ->line('Chúng tôi rất mong nhận được đánh giá của bạn về gia sư. Điều này sẽ giúp cộng đồng học viên khác có thêm thông tin khi lựa chọn gia sư.')
            ->action('Đánh giá ngay', url('/student/bookings/' . $this->bookingData['booking_id']))
            ->line('Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->bookingData['booking_id'],
            'tutor_id' => $this->bookingData['tutor_id'],
            'tutor_name' => $this->bookingData['tutor_name'],
            'subject_name' => $this->bookingData['subject_name'],
            'message' => 'Buổi học ' . $this->bookingData['subject_name'] . ' đã hoàn thành. Hãy đánh giá gia sư ' . $this->bookingData['tutor_name'] . '.',
            'type' => 'booking_completed',
            'action_url' => '/student/bookings/' . $this->bookingData['booking_id']
        ];
    }
} 