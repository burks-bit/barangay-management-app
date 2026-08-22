<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Complaint $complaint)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Complaint Status Updated',
            'message' => "Your complaint {$this->complaint->complaint_code} ({$this->complaint->subject}) is now: "
                . str_replace('_', ' ', $this->complaint->status),
            'type' => 'complaint',
            'complaint_id' => $this->complaint->id,
            'complaint_code' => $this->complaint->complaint_code,
            'status' => $this->complaint->status,
        ];
    }
}