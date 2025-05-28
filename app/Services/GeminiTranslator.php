<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTranslator
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'; // Gemini APIのベースURL

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        // if (empty($this->apiKey)) {
        //     Log::error('GEMINI_API_KEY is not set in .env file.');
        // } else {
        //     Log::info('GEMINI_API_KEY successfully loaded.');
        // }
    }

    /**
     * テキストを翻訳します。
     *
     * @param string $text 翻訳するテキスト
     * @param string $targetLanguage 翻訳先の言語コード (例: 'en', 'ja')
     * @param string $model Geminiモデル名 (例: 'gemini-pro', 'gemini-1.5-pro-latest')
     * @return string|null 翻訳されたテキスト、またはnull
     */
    public function translate(string $text, string $targetLanguage = 'en', string $model = 'gemini-2.0-flash-lite'): ?string
    {
        try {
            // $response = Http::post("{$this->baseUrl}{$model}:generateContent?key={$this->apiKey}", [
            //     'contents' => [
            //         [
            //             'parts' => [
            //                 ['text' => "Translate the following text into {$targetLanguage}: {$text}"]
            //             ]
            //         ]
            //     ]
            // ]);
            // ★リクエストURLとボディをログに出力
            $requestUrl = "{$this->baseUrl}{$model}:generateContent?key={$this->apiKey}";
            $requestBody = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Translate the following text into {$targetLanguage}: {$text}"]
                        ]
                    ]
                ]
            ];
            Log::info('Gemini API Request URL: ' . $requestUrl);
            Log::info('Gemini API Request Body: ' . json_encode($requestBody));

            $response = Http::post($requestUrl, $requestBody);

            // ★レスポンスのステータスコードとボディをログに出力
            Log::info('Gemini API Response Status: ' . $response->status());
            Log::info('Gemini API Response Body: ' . $response->body());

            if ($response->successful()) {
                $translatedText = $response->json('candidates.0.content.parts.0.text');
                return $translatedText;
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return null;
        }
    }
}