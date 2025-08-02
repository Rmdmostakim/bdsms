<?php

namespace RmdMostakim\BdSms\Contracts;

use RmdMostakim\BdSms\Models\Bdsms;

interface SmsInterface
{
    /**
     * Send a message to a single recipient.
     *
     * @param string $to Recipient address.
     * @param string $msg Message content.
     * @return bool True if sent successfully, false otherwise.
     */
    public function single(string $to, string $msg): array;

    /**
     * Send messages to multiple recipients.
     *
     * @param array $to Array of recipient addresses.
     * @param string|array $msg Either a single message (sent to all) or an array of messages.
     * @return array Associative array with recipients as keys and send status as values.
     * @throws InvalidArgumentException If array counts don't match when providing a message array.
     */
    public function multiple(array $to, string|array $msg): array;

}
