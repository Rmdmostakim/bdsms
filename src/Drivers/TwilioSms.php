<?php

namespace RmdMostakim\BdSms\Drivers;

use RmdMostakim\BdSms\Contracts\SmsInterface;
use RmdMostakim\BdSms\Models\Bdsms;
use RmdMostakim\BdSms\Traits\ValidatesPhoneNumber;
use Twilio\Rest\Client;
use InvalidArgumentException;
use Throwable;

class TwilioSms implements SmsInterface
{
    use ValidatesPhoneNumber;

    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $config = config('bdsms.drivers.twilio', []);

        $sid  = $config['sid'] ?? throw new InvalidArgumentException('Twilio SID not configured.');
        $this->from  = $config['number'] ?? throw new InvalidArgumentException('Twilio Number not configured.');
        $sid  = $config['sid'] ?? throw new InvalidArgumentException('Twilio SID not configured.');
        $token = $config['auth_token'] ?? throw new InvalidArgumentException('Twilio Auth Token not configured.');

        $this->client = new Client($sid, $token);
    }

    public function single(string $to, string $msg): array
    {
        try {
            $this->validateMessage($to, $msg);
            $to = $this->validatePhoneNumber($to);

            $sms = Bdsms::create([
                'driver'  => 'twilio',
                'to'      => $to,
                'message' => $msg,
            ]);

            $twilioMessage = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $msg,
            ]);

            $sms->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            return ['status' => true, 'uuid' => $sms->uuid, 'sid' => $twilioMessage->sid];
        } catch (Throwable $e) {
            $sms->update([
                'status'  => 'failed',
                'sent_at' => now(),
                'error_message'   => $e->getMessage(),
            ]);
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    public function multiple(array $to, string|array $msg): array
    {
        if (empty($to) || empty($msg)) {
            throw new InvalidArgumentException('Recipient and message cannot be empty.');
        }

        $results = [];

        foreach ($to as $index => $recipient) {
            $message = is_array($msg) ? ($msg[$index] ?? '') : $msg;
            $results[$recipient] = $this->single($recipient, $message);
        }

        return $results;
    }

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
