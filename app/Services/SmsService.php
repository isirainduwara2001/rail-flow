<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $userId;
    protected string $password;
    protected string $baseUrl = 'http://textit.biz/sendmsg';

    public function __construct()
    {
        
        $this->userId = config('services.textit.user_id') ?? '';
        $this->password = config('services.textit.password') ?? '';
    }

    /**
     * Send SMS via Textit.biz
     *
     * @param string $to Phone number
     * @param string $message Message content
     * @return bool
     */
    public function sendSms(string $to, string $message): bool
    {
        if (empty($this->userId) || empty($this->password)) {
            Log::error('SMS Service Error: Textit credentials not configured.');
            return false;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'id' => $this->userId,
                'pw' => $this->password,
                'to' => $to,
                'text' => $message
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$to}");
                return true;
            }

            Log::error("SMS sending failed to {$to}: " . $response->body());
            return false;
        } catch (Exception $e) {
            Log::error("SMS Service Exception: " . $e->getMessage());
            return false;
        }
    }
}
