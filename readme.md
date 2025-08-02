
# Project Title

A brief description of what this project does and who it's for

# 📦 BdSms

A flexible and extensible Laravel package for sending SMS through multiple Bangladeshi and international gateways — including Twilio, SSL Wireless, MimSMS, Infobip.

---
## 🛠 Supported Gateway
- ✅ Twilio
- ✅ MimSMS
- ✅ SslWireless
- ✅ Infobip  
---
## 🚀 Installation

```bash
composer require rmdmostakim/bdsms
```

---

## 🔧 Configuration

### 1. Publish Config File
```bash
 php artisan vendor:publish --provider="RmdMostakim\BdSms\SmsServiceProvider"
```

This will publish `config/bdsms.php`.

### 2. Run Migration

```bash
php artisan migrate
```

### 3. Set Up .env Values

#### ✅ Example for Twilio:
```env
#twilio credentials
TWILIO_ACCOUNT_SID="your-twilio-sid"
TWILIO_AUTH_TOKEN="your-twilio-auth-token"
TWILIO_NUMBER="your-twilio-number"
```
#### ✅ Example for SSL Wireless:
```env
#ssl wireless credentials
SSL_WIRELESS_API_TOKEN="your_ssl_api_key"
SSL_WIRELESS_SID="your_ssl_username"
SSL_WIRELESS_API_URL="https://smsplus.sslwireless.com/api/v3/send-sms"
```

#### ✅ Example for MimSMS:
```env
#mim sms credentials
MIM_SMS_API_KEY="your-mimsms-app-key"
MIM_SMS_USER_NAME="your-mimsms-user-name"
MIM_SMS_SENDER_NAME="your-mimsms-name"
MIM_SMS_API_URL="https://api.mimsms.com/api/SmsSending/SMS"
```

#### ✅ Example for Infobip:
```env
#infobip credentials
INFOBIP_API_KEY="your-infobip-api-key"
INFOBIP_API_URL="your-infobip-api-url"
INFOBIP_NUMBER="your-infobip-number"
```

---

## ✅ Usage

### 🔹 Using Facade (`BdSms::TwilioSms()->single()`)

#### ✅ Send single SMS using Twilio:
```php
use RmdMostakim\BdSms\Facades\BdSms;

BdSms::TwilioSms()->single('+8801XXXXXXXXX', 'Hello from Twilio!');
```

#### ✅ Send using SSL Wireless:
```php
BdSms::SslWireless()->single('017XXXXXXXX', 'SSL SMS test');
```

#### ✅ Send using MimSMS (default type = 'D'):
```php
BdSms::MimSms()->single('017XXXXXXXX', 'MimSMS message');
```

#### ✅ Send using Infobip:
```php
BdSms::InfobipSms()->single('017XXXXXXXX', 'Infobip test message');
```

#### ✅ Multiple messages:
📤 Send to Multiple Recipients with a Single Message
```php
BdSms::TwilioSms()->multiple(['017XXXXXXXX', '017YYYYYYYY'],'Hello World!');
```
📤 Send Personalized Messages to Multiple Recipients
```php
BdSms::TwilioSms()->multiple(
    ['017XXXXXXXX', '017YYYYYYYY'],
    ['Hello A', 'Hello B']
);
```

#### ✅ Check SMS Status by UUID:
```php
$status = BdSms::check('your-sms-uuid');

if ($status?->status === 'sent') {
    // Delivered or accepted
}
```

---

## 🧩 Dependency Injection

You can inject `SmsManager` into a controller or service:

```php
use RmdMostakim\BdSms\SmsManager;

class SmsService
{
    public function __construct(protected SmsManager $sms) {}

    public function notifyUser(string $phone)
    {
        $this->sms->TwilioSms()->single($phone, 'Your order has been placed!');
    }
}
```

---

## 📦 Drivers Supported

| Method             | Gateway       |
|--------------------|---------------|
| `TwilioSms()`      | Twilio        |
| `SslWireless()`    | SSL Wireless  |
| `MimSms($type)`    | MimSMS        |
| `InfobipSms()`     | Infobip       |

Each returns a driver instance that supports:
- `single(string $to, string $msg): array`
- `multiple(array $to, string|array $msg): array`

---

## 🅾️ MimSMS Transaction Type

The `MimSms` driver requires a transaction type which defines the **category of SMS** you want to send. This value is passed into the constructor as a single-character string.

#### ✅ Available types:

| Type | Description             |
|------|-------------------------|
| `T`  | **Transactional** SMS   |
| `P`  | **Promotional** SMS     |
| `D`  | **Dynamic Masking** SMS |

> Default is `'D'` if not provided.

#### 📌 Example usage:

```php
BdSms::MimSms()->single('017XXXXXXXX', 'Default D type SMS');
BdSms::MimSms('P')->single('017XXXXXXXX', 'Promotional SMS');
```

---

## 🧪 Build Your Own Driver

Implement the interface:

```php
use RmdMostakim\BdSms\Contracts\SmsInterface;

class CustomSmsDriver implements SmsInterface
{
    public function single(string $to, string $msg): array {
        // implement
    }

    public function multiple(array $to, string|array $msg): array {
        // implement
    }
}
```

---

## 📜 License

MIT © [RmdMostakim](https://github.com/rmdmostakim)
