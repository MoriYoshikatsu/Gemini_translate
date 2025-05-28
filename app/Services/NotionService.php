<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotionService
{
    protected $apiKey;
    protected $databaseId;
    protected $baseUrl = 'https://api.notion.com/v1/';

    public function __construct()
    {
        $this->apiKey = env('NOTION_API_KEY');
        $this->databaseId = env('NOTION_DATABASE_ID');
    }

    /**
     * Notionデータベースに新しいページを作成します。
     *
     * @param string $originalText 翻訳元のテキスト
     * @param string $translatedText 翻訳されたテキスト
     * @param string $sourceLanguage 翻訳元言語（オプション）
     * @param string $targetLanguage 翻訳先言語（オプション）
     * @return array|null 作成されたページのデータ、またはnull
     */
    public function createPage(string $originalText, string $translatedText, string $sourceLanguage = null, string $targetLanguage = null): ?array
    {
        try {
            $properties = [
                'Original Text' => [
                    'title' => [
                        [
                            'text' => ['content' => $originalText]
                        ]
                    ]
                ],
                'Translated Text' => [
                    'rich_text' => [
                        [
                            'text' => ['content' => $translatedText]
                        ]
                    ]
                ],
                // 必要に応じて、Notionデータベースのプロパティを追加
                // 例: 'Source Language' のTextプロパティがある場合
                // 'Source Language' => [
                //     'rich_text' => [
                //         [
                //             'text' => ['content' => $sourceLanguage]
                //         ]
                //     ]
                // ],
                // 'Target Language' のTextプロパティがある場合
                // 'Target Language' => [
                //     'rich_text' => [
                //         [
                //             'text' => ['content' => $targetLanguage]
                //         ]
                //     ]
                // ],
                'Date' => [
                    'date' => [
                        'start' => now()->toDateString()
                    ]
                ],
            ];

            // Notionのプロパティ名に合わせて調整してください
            // 例: Notionのプロパティが "元の文章" なら '元の文章' => ...
            // 例: Notionのプロパティが "翻訳された文章" なら '翻訳された文章' => ...
            // タイプが "Title" のプロパティは1つのみにしてください。

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Notion-Version' => '2022-06-28', // 最新のNotion APIバージョンを指定
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}pages", [
                'parent' => [
                    'database_id' => $this->databaseId,
                ],
                'properties' => $properties,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                \Log::error('Notion API Error: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('Notion API Exception: ' . $e->getMessage());
            return null;
        }
    }
}