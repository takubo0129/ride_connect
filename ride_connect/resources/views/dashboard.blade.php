<x-app-layout>
    <x-slot name="header">
        <div class="space-y-2">
            <div class="rc-glow-tag">DASHBOARD</div>
            <h1 class="text-3xl font-semibold text-slate-900 leading-tight">
                {{ __('Dashboard') }}
            </h1>
            <p class="text-sm text-slate-500">最新のコネクト情報とおすすめにアクセスしましょう。</p>
        </div>
    </x-slot>

    <div class="rc-shell">
        <div class="rc-card p-8 sm:p-10">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-3">
                    <p class="rc-glow-tag">Next Ride</p>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __("You're logged in!") }}</h2>
                    <p class="text-sm leading-relaxed text-slate-600">最新のコネクト情報をチェックして、あなたのモビリティ体験をアップデートしましょう。</p>
                </div>
                <a href="{{ route('vehicles.index') }}" class="rc-button-primary">車両を探す</a>
            </div>
        </div>
    </div>
</x-app-layout>
