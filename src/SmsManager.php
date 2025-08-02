<?php

namespace RmdMostakim\BdSms;

use RmdMostakim\BdSms\Drivers\InfobipSms;
use RmdMostakim\BdSms\Drivers\MimSms;
use RmdMostakim\BdSms\Drivers\SslWireless;
use RmdMostakim\BdSms\Drivers\TwilioSms;
use RmdMostakim\BdSms\Models\Bdsms;
use Throwable;

class SmsManager
{
    public function MimSms(string $type = 'D'): MimSms
    {
        return new MimSms($type);
    }

    public function SslWireless(): SslWireless
    {
        return new SslWireless();
    }

    public function TwilioSms(): TwilioSms
    {
        return new TwilioSms();
    }
    public function InfobipSms(): InfobipSms
    {
        return new InfobipSms();
    }

    public function check(string $uuid): ?Bdsms
    {
        try {
            return Bdsms::where('uuid', $uuid)->first();
        } catch (Throwable $e) {
            return null;
        }
    }
}
