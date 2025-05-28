<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gemini 翻訳 & Notion 登録') }}
        </h2>
    </x-slot>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        textarea { width: 100%; height: 150px; margin-bottom: 10px; padding: 8px; box-sizing: border-box; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], select { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .alert { padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
    <div class="container">
        <h1>Gemini 翻訳 & Notion 自動登録アプリ</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('translate.submit') }}" method="POST">
            @csrf
            <div>
                <label for="text">翻訳したい文章:</label>
                <textarea id="text" name="text" required placeholder="ここに文章を入力してください">{{ old('text') }}</textarea>
            </div>
            <div>
                <label for="source_language">翻訳元言語 (オプション、例: ja):</label>
                <input type="text" id="source_language" name="source_language" value="{{ old('source_language', 'ja') }}">
            </div>
            <div>
                <label for="target_language">翻訳先言語 (必須、例: en):</label>
                <input type="text" id="target_language" name="target_language" required value="{{ old('target_language', 'en') }}">
            </div>
            <button type="submit">翻訳してNotionに登録</button>
        </form>
    </div>
</x-app-layout>