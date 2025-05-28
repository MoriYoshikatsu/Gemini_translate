<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Translation;
use App\Services\GeminiTranslator;
use App\Services\NotionService;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    protected $geminiTranslator;
    protected $notionService;

    public function __construct(GeminiTranslator $geminiTranslator, NotionService $notionService)
    {
        $this->geminiTranslator = $geminiTranslator;
        $this->notionService = $notionService;
    }

    /**
     * 翻訳フォームを表示します。
     */
    public function showForm()
    {
        return view('translate');
    }

    /**
     * 翻訳を実行し、データベースに保存し、Notionに登録します。
     */
    public function translate(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'target_language' => 'required|string|min:2|max:5', // 例: en, ja
            'source_language' => 'nullable|string|min:2|max:5', // オプション
        ]);

        $originalText = $request->input('text');
        $targetLanguage = $request->input('target_language');
        $sourceLanguage = $request->input('source_language');

        // 1. Geminiで翻訳
        $translatedText = $this->geminiTranslator->translate($originalText, $targetLanguage);

        if (!$translatedText) {
            return back()->withErrors(['translation_error' => '翻訳に失敗しました。Gemini APIの設定を確認してください。']);
        }

        // 2. データベースに保存
        try {
            $translation = Translation::create([
                'original_text' => $originalText,
                'translated_text' => $translatedText,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
            ]);
            Log::info('Translation saved to DB: ' . $translation->id);
        } catch (\Exception $e) {
            Log::error('Failed to save translation to DB: ' . $e->getMessage());
            // データベース保存が失敗しても、Notion登録は試みる
        }

        // 3. Notionに登録
        $notionPage = $this->notionService->createPage($originalText, $translatedText, $sourceLanguage, $targetLanguage);

        if (!$notionPage) {
            return back()->withErrors(['notion_error' => 'Notionへの登録に失敗しました。Notion APIの設定を確認してください。']);
        }

        return back()->with('success', '翻訳が完了し、Notionに登録されました！');
    }
}
