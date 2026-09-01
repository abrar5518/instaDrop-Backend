<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    /**
     * Normalize phone numbers to E.164 digits-only format.
     */
    public static function formatE164(string $phoneNumber): string
    {
        return preg_replace('/\D/', '', $phoneNumber) ?? '';
    }

    /**
     * Validate WhatsApp configuration.
     */
    public function validateConfig(): array
    {
        $enabled = config('whatsapp.enabled', true);
        $graphVersion = config('whatsapp.graph_version', 'v25.0');
        $token = config('whatsapp.access_token');
        $phoneId = config('whatsapp.phone_number_id');
        $adminNumber = config('whatsapp.admin_number');

        $errors = [];

        if (!$enabled) {
            $errors[] = 'WhatsApp notifications are disabled (WHATSAPP_ENABLED=false).';
        }
        if (empty($token)) {
            $errors[] = 'WhatsApp Access Token (WHATSAPP_ACCESS_TOKEN) is missing.';
        }
        if (empty($phoneId)) {
            $errors[] = 'WhatsApp Phone Number ID (WHATSAPP_PHONE_NUMBER_ID) is missing.';
        }
        if (empty($adminNumber)) {
            $errors[] = 'WhatsApp Admin Number (WHATSAPP_ADMIN_NUMBER) is missing.';
        }
        if (empty($graphVersion)) {
            $errors[] = 'WhatsApp Graph API version (WHATSAPP_GRAPH_VERSION) is missing.';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'config' => [
                'enabled' => $enabled,
                'graph_version' => $graphVersion,
                'phone_number_id' => $phoneId,
                'admin_number' => static::formatE164((string) $adminNumber),
                'has_token' => !empty($token),
            ],
        ];
    }

    /**
     * Send approved WhatsApp message template via Meta Graph API v25.0.
     *
     * @param string $recipient
     * @param string $templateName
     * @param array<string> $bodyParameters
     * @param string $languageCode
     * @return array{success: bool, message_id: ?string, error: ?string}
     */
    public function sendTemplate(
        string $recipient,
        string $templateName,
        array $bodyParameters,
        string $languageCode = 'en'
    ): array {
        $validation = $this->validateConfig();

        if (!$validation['valid']) {
            $errorMessage = implode(' ', $validation['errors']);
            Log::warning('[WhatsAppCloudApiService] Configuration invalid or disabled', [
                'errors' => $validation['errors'],
                'recipient' => static::formatE164($recipient),
                'template' => $templateName,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $errorMessage,
            ];
        }

        $formattedRecipient = static::formatE164($recipient);
        if (empty($formattedRecipient)) {
            Log::error('[WhatsAppCloudApiService] Invalid recipient number', [
                'raw_recipient' => $recipient,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Invalid recipient phone number.',
            ];
        }

        $graphVersion = config('whatsapp.graph_version', 'v25.0');
        $phoneId = config('whatsapp.phone_number_id');
        $token = config('whatsapp.access_token');

        $url = "https://graph.facebook.com/{$graphVersion}/{$phoneId}/messages";

        $parametersPayload = array_map(function ($val) {
            return [
                'type' => 'text',
                'text' => (string) $val,
            ];
        }, $bodyParameters);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $formattedRecipient,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $parametersPayload,
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->retry(2, 500, function ($exception, $request) {
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        $status = $exception->response->status();
                        return $status === 429 || $status >= 500;
                    }
                    return false;
                }, throw: false)
                ->post($url, $payload);

            if ($response->successful()) {
                $messageId = $response->json('messages.0.id');

                Log::info('[WhatsAppCloudApiService] Message sent successfully', [
                    'recipient' => $formattedRecipient,
                    'template' => $templateName,
                    'meta_message_id' => $messageId,
                ]);

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'error' => null,
                ];
            }

            $status = $response->status();
            $body = $response->json() ?? [];
            $errorMessage = $body['error']['message'] ?? $response->body();

            Log::error('[WhatsAppCloudApiService] Meta API error response', [
                'status' => $status,
                'recipient' => $formattedRecipient,
                'template' => $templateName,
                'error_code' => $body['error']['code'] ?? null,
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => "Meta API HTTP {$status}: {$errorMessage}",
            ];
        } catch (\Throwable $e) {
            Log::error('[WhatsAppCloudApiService] Request exception occurred', [
                'recipient' => $formattedRecipient,
                'template' => $templateName,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
