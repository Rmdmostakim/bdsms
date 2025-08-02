<?php

namespace RmdMostakim\BdSms\Drivers;

use RmdMostakim\BdSms\Contracts\SmsInterface;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RmdMostakim\BdSms\Models\Bdsms;
use RmdMostakim\BdSms\Traits\ValidatesPhoneNumber;
use Throwable;

class MimSms implements SmsInterface
{
    use ValidatesPhoneNumber;

    protected string $apiKey;
    protected string $userName;
    protected string $senderName;
    protected string $apiUrl;
    protected string $transactionType;

    public function __construct(string $transactionType = 'D')
    {
        $allowedTypes = ['T', 'D', 'P'];
        if (!in_array($transactionType, $allowedTypes, true)) {
            throw new InvalidArgumentException("Invalid transaction type: {$transactionType}. Allowed values are: T, D, P.");
        }

        $config = config('bdsms.drivers.mimsms', []);

        $this->apiKey         = $config['api_key'] ?? throw new InvalidArgumentException('MimSMS API key is not set.');
        $this->userName       = $config['user_name'] ?? throw new InvalidArgumentException('MimSMS username is not set.');
        $this->senderName     = $config['sender_name'] ?? throw new InvalidArgumentException('MimSMS sender name is not set.');
        $this->apiUrl        = rtrim($config['api_url'] ?? '', '/');
        $this->transactionType = $transactionType;

        if (empty($this->apiUrl)) {
            throw new InvalidArgumentException('MimSMS API URL is not set.');
        }
    }

    /**
     * Send a single SMS message.
     */
    public function single(string $to, string $msg): array
    {
        try {
            $this->validateMessage($to, $msg);
            $to = $this->validatePhoneNumber($to);

            $sms = Bdsms::create([
                'driver'  => 'mimsms',
                'to'      => $to,
                'message' => $msg,
            ]);

            $response = $this->sendRequest($to, $msg);

            $success = is_array($response) && ($response['status'] ?? '') === 'Success';

            $sms->update([
                'status'        => $success ? 'sent' : 'failed',
                'sent_at'       => now(),
                'error_message' => $success ? null : ($response['responseResult'] ?? 'Unknown error'),
            ]);

            return ['status' => $success, 'uuid' => $sms->uuid];
        } catch (Throwable $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send multiple SMS messages.
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
     * Send HTTP request to MimSMS API.
     */
    protected function sendRequest(string $to, string $msg): array
    {

        $payload = [
            'UserName'        => $this->userName,
            'Apikey'          => $this->apiKey,
            'MobileNumber'    => $to,
            'SenderName'      => $this->senderName,
            'TransactionType' => $this->transactionType,
            'Message'         => $msg,
        ];

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload)->throw();

            return $response->json();
        } catch (Throwable $e) {
            return ['status' => 'Failed', 'responseResult' => $e->getMessage()];
        }
    }

    /**
     * Validate recipient and message content.
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
