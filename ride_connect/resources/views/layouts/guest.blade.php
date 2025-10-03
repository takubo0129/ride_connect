<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-40 right-[-10%] h-[520px] w-[520px] rounded-full bg-emerald-300/20 blur-3xl"></div>
                <div class="absolute -bottom-32 left-[-10%] h-[460px] w-[460px] rounded-full bg-sky-300/20 blur-3xl"></div>
            </div>

            <div class="relative mx-auto grid min-h-screen w-full max-w-6xl items-center gap-12 px-6 py-16 sm:px-8 md:px-10 lg:grid-cols-[1.1fr_1fr]">
                <div class="hidden lg:flex flex-col gap-6">
                    <a href="/" class="inline-flex w-fit items-center gap-3 rounded-full border border-white/60 bg-white/80 px-4 py-3 text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700 shadow-sm">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500">
                            <img src="{{ asset('images/ride-connect-logo.svg') }}" alt="Ride Connect ロゴ" class="h-6 w-6">
                        </span>
                        ライドコネクト
                    </a>
                    <h1 class="text-4xl font-semibold leading-tight text-slate-900">
                        モビリティのシェア体験を、
                        <span class="text-emerald-600">もっと気軽に。</span>
                    </h1>
                    <p class="max-w-lg text-base leading-relaxed text-slate-600">
                        ライドコネクトは、車やバイクへの情熱を共有するコミュニティです。安全でシンプルな取引画面で、あなたの憧れの一台に出会いましょう。
                    </p>
                    <div class="mt-4 flex items-center gap-3 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-2">
                            <span class="text-lg">⭐️</span> 安心の本人確認フロー
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-2">
                            <span class="text-lg">🔒</span> 取引とメッセージを一元管理
                        </span>
                    </div>
                </div>

                <div class="relative w-full">
                    <div class="absolute inset-0 -z-10 rounded-[36px] bg-white/40 blur-3xl"></div>
                    <div class="relative rounded-[32px] border border-slate-200/70 bg-white/95 p-8 shadow-xl shadow-slate-900/5 backdrop-blur-sm">
                        <div class="mb-8 space-y-3 text-center lg:hidden">
                            <a href="/" class="inline-flex h-20 w-20 items-center justify-center rounded-full border border-emerald-400 text-sm font-semibold uppercase tracking-[0.24em] text-emerald-600">
                                RC
                            </a>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-500">Ride Connect</p>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
