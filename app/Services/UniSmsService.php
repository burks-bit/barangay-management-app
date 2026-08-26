<?php

namespace App\Services;

use App\Models\BarangayOfficial;
use App\Models\IncidentBlotter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UniSmsService
{
    public function notifyOfficials(IncidentBlotter $blotter): void
    {
        $apiKey = config('services.unisms.key');

        if (! $apiKey) {
            Log::warning('Incident SMS notifications skipped because the UniSMS API key is not configured.', [
                'blotter_id' => $blotter->id,
                'blotter_code' => $blotter->blotter_code,
            ]);

            return;
        }

        $officials = BarangayOfficial::query()
            ->where('is_active', true)
            ->whereNotNull('contact_number')
            ->where('contact_number', '<>', '')
            ->get(['id', 'contact_number']);

        $content = sprintf(
            'New incident %s: %s at %s. Please review the barangay blotter.',
            $blotter->blotter_code,
            $blotter->title,
            $blotter->location
        );

        foreach ($officials as $official) {
            $recipient = $this->toE164($official->contact_number);

            if (! $recipient) {
                Log::warning('Incident SMS skipped because an official phone number is invalid.', [
                    'official_id' => $official->id,
                    'blotter_id' => $blotter->id,
                ]);

                continue;
            }

            try {
                $response = Http::withBasicAuth($apiKey, '')
                    ->acceptJson()
                    ->timeout(15)
                    ->post(config('services.unisms.url'), [
                        'recipient' => $recipient,
                        'content' => mb_substr($content, 0, 160),
                        'sender_id' => config('services.unisms.sender_id'),
                        'metadata' => [
                            'type' => 'incident_blotter',
                            'blotter_id' => (string) $blotter->id,
                        ],
                    ]);

                if ($response->successful()) {
                    Log::info('Incident SMS sent to barangay official.', [
                        'official_id' => $official->id,
                        'blotter_id' => $blotter->id,
                        'blotter_code' => $blotter->blotter_code,
                        'recipient' => $recipient,
                        'status' => $response->status(),
                        'reference_id' => $response->json('message.reference_id'),
                    ]);
                } else {
                    Log::error('Incident SMS request was rejected by UniSMS.', [
                        'official_id' => $official->id,
                        'blotter_id' => $blotter->id,
                        'recipient' => $recipient,
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);
                }
            } catch (\Throwable $exception) {
                Log::error('Incident SMS request failed.', [
                    'official_id' => $official->id,
                    'blotter_id' => $blotter->id,
                    'recipient' => $recipient,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function toE164(string $phoneNumber): ?string
    {
        $phoneNumber = preg_replace('/[\s().-]+/', '', trim($phoneNumber));

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '+63' . substr($phoneNumber, 1);
        } elseif (str_starts_with($phoneNumber, '63')) {
            $phoneNumber = '+' . $phoneNumber;
        }

        return preg_match('/^\+[1-9]\d{7,14}$/', $phoneNumber) ? $phoneNumber : null;
    }
}