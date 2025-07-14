<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use Illuminate\Notifications\Messages\DatabaseMessage;
class NewTaskAssignedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public Task $task;
    public function __construct(Task $task)
    {
        //
        $this->task = $task;
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
            'task_id' => $this->task->id,
            'task_type' => $this->task->type,
            'description' => $this->task->description,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date ? (method_exists($this->task->due_date, 'format') ? $this->task->due_date->format('Y-m-d') : (string) $this->task->due_date) : null,
            'assigned_by' => 'System', // You could add user_id of the assigner if needed
            'link' => route('tasks.show', $this->task->id), // Assuming task show route
        ];
    }
}
