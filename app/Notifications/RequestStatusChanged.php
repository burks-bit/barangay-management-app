<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public ServiceRequest $serviceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Request Status Updated',
            'message' => "Your request {$this->serviceRequest->tracking_number} ({$this->serviceRequest->requestType?->name}) is now: "
                . str_replace('_', ' ', $this->serviceRequest->status),
            'type' => 'request',
            'request_id' => $this->serviceRequest->id,
            'tracking_number' => $this->serviceRequest->tracking_number,
            'status' => $this->serviceRequest->status,
        ];
    }
}