<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>完了 | ライコネ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-900">
    <div class="min-h-screen flex flex-col items-center justify-center text-center px-6 bg-slate-50">
        <div class="space-y-6">
            <div class="h-24 w-24 rounded-full border border-emerald-400 flex items-center justify-center text-xl font-semibold mx-auto bg-white shadow-xl">
                ライコネ
            </div>
            <h1 class="text-3xl font-semibold text-slate-900">準備は完了しました！</h1>
            <p class="text-slate-600">憧れのライドとつながる冒険が、今まさに始まろうとしています。</p>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-emerald-400 text-slate-900 font-semibold shadow hover:bg-emerald-300 transition">
                <span>ホームへ進む</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</body>
</html>
