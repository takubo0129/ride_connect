<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900">アカウントを作成</h1>
            <p class="text-sm text-slate-500">レンタル体験をはじめるために、基本情報を登録しましょう。</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="name" class="block text-sm text-slate-600">氏名</label>
                <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                       class="rc-input">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-sm text-slate-600">メールアドレス</label>
                <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                       class="rc-input">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm text-slate-600">パスワード</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="rc-input">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm text-slate-600">パスワード（確認）</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="rc-input">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="space-y-3">
                <button type="submit" class="w-full rounded-xl bg-emerald-500 text-white font-semibold py-3 hover:bg-emerald-400 transition">
                    登録する
                </button>
                <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center rounded-xl border border-emerald-500 py-3 text-emerald-600 hover:bg-emerald-50 transition">
                    すでにアカウントをお持ちの方はこちら
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
