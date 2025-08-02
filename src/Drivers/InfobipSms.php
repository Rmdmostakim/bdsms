<?php

namespace RmdMostakim\BdSms\Drivers;

use RmdMostakim\BdSms\Contracts\SmsInterface;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RmdMostakim\BdSms\Models\Bdsms;
use RmdMostakim\BdSms\Traits\ValidatesPhoneNumber;
use Throwable;

class InfobipSms implements SmsInterface
{
    use ValidatesPhoneNumber;

    protected string $apiUrl;
    protected string $from;
    protected string $apiKey;

    public function __construct()
    {
        $config = config('bdsms.drivers.infobip', []);

        $this->apiKey  = $config['api_key'] ?? throw new InvalidArgumentException('Infobip API key is missing.');
        $this->from    = $config['number'] ?? throw new InvalidArgumentException('Infobip Number is missing.');
        $this->apiUrl = rtrim($config['api_url'] ?? '', '/');

        if (empty($this->apiUrl)) {
            throw new InvalidArgumentException('Infobip API URL is not set.');
        }
    }

    /**
     * Send a single SMS
     */
    public function single(string $to, string $msg): array
    {
        try {
            $this->validateMessage($to, $msg);

            $to = $this->validatePhoneNumber($to);

            $sms = Bdsms::create([
                'driver'  => 'infobip',
                'to'      => $to,
                'message' => $msg,
            ]);

            $response = $this->sendRequest($to, $msg);

            $sms->update([
                'status'        => 'sent',
                'sent_at'       => now(),
            ]);

            return ['status' => true, 'uuid' => $sms->uuid];
        } catch (Throwable $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send multiple SMS
     */
    public function multiple(array $recipients, string|array $messages): array
    {
        if (empty($recipients) || empty($messages)) {
            throw new InvalidArgumentException('Recipients and messages cannot be empty.');
        }

        $results = [];

        foreach ($recipients as $index => $to) {
            $msg = is_array($messages)
                ? ($messages[$index] ?? '')
                : $messages;

            $results[$to] = $this->single($to, $msg);
        }

        return $results;
    }

    /**
     * Send HTTP request to Infobipsms API
     */
    protected function sendRequest(string $to, string $msg): array
    {

        $payload = [
            'messages' => [
                [
                    'from' => $this->from,
                    'destinations' => [
                        ['to' => $to]
                    ],
                    'text' => $msg
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "App {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl, $payload)->throw();
            return $response->json();
        } catch (Throwable $e) {
            return ['status' => 'Failed', 'error_message' => $e->getMessage()];
        }
    }

    /**
     * Validate message and recipient
     */
    protected function validateMessage(string $to, string $msg): void
    {
        if (empty($to)) {
            throw new InvalidArgumentException('Recipient number is required.');
        }

        if (empty($msg)) {
            throw new InvalidArgumentException('Message cannot be empty.');
        }

        if (strlen($msg) > 1600) {
            throw new InvalidArgumentException('Message exceeds 1600 characters.');
        }
    }
}
