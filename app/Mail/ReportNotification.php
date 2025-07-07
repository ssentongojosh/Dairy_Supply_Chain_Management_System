<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment; // For file attachments
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Report;
use App\Models\User; // To pass user data to the view
use Illuminate\Support\Facades\Storage;

class ReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $user; // To pass user name/details to the email view

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, User $user)
    {
        $this->report = $report;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Scheduled Report: ' . $this->report->report_name,
            // You can set from/replyTo here if different from default Mail config
            // from: new Address('no-reply@yourdomain.com', 'Your App Name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // This specifies the Blade view for your email content
        return new Content(
            view: 'emails.reports.notification',
            with: [
                'report' => $this->report,
                'userName' => $this->user->name, // Pass user's name for personalization
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Attach the generated report file
        if ($this->report->file_path && Storage::disk('local')->exists($this->report->file_path)) {
            return [
                Attachment::fromStorageDisk('local', $this->report->file_path)
                          ->as($this->report->file_name)
                          ->withMime($this->report->format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf'),
            ];
        }
        return [];
    }
}