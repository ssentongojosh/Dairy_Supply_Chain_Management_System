<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ReportNotification;

class ReportGeneratedSystemNotification extends Notification
{
    use Queueable;

    protected $reportNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReportNotification $reportNotification)
    {
        $this->reportNotification = $reportNotification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_generated',
            'title' => 'Report Generated Successfully',
            'message' => "Your {$this->reportNotification->report_name} report has been generated and is ready for download.",
            'report_notification_id' => $this->reportNotification->id,
            'report_name' => $this->reportNotification->report_name,
            'report_types' => $this->reportNotification->formatted_report_types,
            'format' => strtoupper($this->reportNotification->format),
            'file_size' => $this->reportNotification->formatted_file_size,
            'generated_at' => $this->reportNotification->generated_at->format('M j, Y g:i A'),
            'download_url' => route('reports.download', $this->reportNotification->id),
            'icon' => 'fas fa-file-alt',
            'action_text' => 'Download Report',
            'action_url' => route('reports.download', $this->reportNotification->id)
        ];
    }
}
