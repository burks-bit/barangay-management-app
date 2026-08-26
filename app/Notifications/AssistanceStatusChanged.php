<?php

namespace App\Notifications;

use App\Models\AssistanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssistanceStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public AssistanceRequest $assistanceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Assistance Request Updated',
            'message' => "Your assistance request {$this->assistanceRequest->assistance_code} ({$this->assistanceRequest->assistanceType?->name}) is now: "
                . str_replace('_', ' ', $this->assistanceRequest->status),
            'type' => 'assistance',
            'assistance_id' => $this->assistanceRequest->id,
            'assistance_code' => $this->assistanceRequest->assistance_code,
            'status' => $this->assistanceRequest->status,
        ];
    }
}