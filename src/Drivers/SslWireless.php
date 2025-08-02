<?php

namespace RmdMostakim\BdSms\Drivers;

use RmdMostakim\BdSms\Contracts\SmsInterface;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RmdMostakim\BdSms\Models\Bdsms;
use RmdMostakim\BdSms\Traits\ValidatesPhoneNumber;
use Throwable;

class SslWireless implements SmsInterface
{
    use ValidatesPhoneNumber;

    protected string $apiUrl;
    protected string $sid;
    protected string $token;

    public function __construct()
    {
        $config = config('bdsms.drivers.sslwireless', []);

        $this->sid     = $config['sid'] ?? throw new InvalidArgumentException('SSLWireless SID is not set.');
        $this->token   = $config['api_token'] ?? throw new InvalidArgumentException('SSLWireless API token is not set.');
        $this->apiUrl = rtrim($config['api_url'] ?? '', '/');

        if (empty($this->apiUrl)) {
            throw new InvalidArgumentException('SSLWireless API URL is not set.');
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
                'driver'  => 'sslwireless',
                'to'      => $to,
                'message' => $msg,
            ]);

            $response = $this->sendRequest($to, $msg, $sms->uuid);

            $success = is_array($response) && ($response['status'] ?? '') === 'Success';

            $sms->update([
                'status'        => $success ? 'sent' : 'failed',
                'sent_at'       => now(),
                'error_message' => $success ? null : ($response['error_message'] ?? 'Unknown error'),
            ]);

            return ['status' => $success, 'uuid' => $sms->uuid];
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
     * Send HTTP request to SSLWireless API
     */
    protected function sendRequest(string $to, string $msg, string $uuid): array
    {

        $payload = [
            'api_token' => $this->token,
            'sid'       => $this->sid,
            'msisdn'    => $to,
            'sms'       => $msg,
            'csms_id'   => $uuid,
        ];

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
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
