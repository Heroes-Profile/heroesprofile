<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected $secretKey;

    protected $scoreThreshold;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key');
        $this->scoreThreshold = config('services.recaptcha.score_threshold', 0.5);
    }

    /**
     * Verify reCAPTCHA v3 token
     */
    public function verify(string $token, string $action = 'contact_form', ?Request $request = null): array
    {
        $request = $request ?? request();
        $ip = WhitelistedIPsService::getClientIp($request);

        if (empty($this->secretKey)) {
            Log::warning('reCAPTCHA secret key not configured');

            return ['success' => false, 'error' => 'reCAPTCHA not configured'];
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            $data = $response->json();

            if (! $data['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'errors' => $data['error-codes'] ?? [],
                    'ip' => $ip,
                ]);

                return ['success' => false, 'error' => 'reCAPTCHA verification failed'];
            }

            // Check score for reCAPTCHA v3
            $score = $data['score'] ?? 0;
            if ($score < $this->scoreThreshold) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $score,
                    'threshold' => $this->scoreThreshold,
                    'ip' => $ip,
                ]);

                return ['success' => false, 'error' => 'reCAPTCHA score too low', 'score' => $score];
            }

            // Check action
            if ($data['action'] !== $action) {
                Log::warning('reCAPTCHA action mismatch', [
                    'expected' => $action,
                    'received' => $data['action'],
                    'ip' => $ip,
                ]);

                return ['success' => false, 'error' => 'reCAPTCHA action mismatch'];
            }

            return [
                'success' => true,
                'score' => $score,
                'action' => $data['action'],
            ];

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);

            return ['success' => false, 'error' => 'reCAPTCHA verification error'];
        }
    }

    /**
     * Get site key for frontend
     */
    public function getSiteKey(): ?string
    {
        return config('services.recaptcha.site_key');
    }
}
