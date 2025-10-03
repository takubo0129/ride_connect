<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900">ログイン</h1>
            <p class="text-sm text-slate-500">登録済みのメールアドレスとパスワードを入力してください。</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="email" class="block text-sm text-slate-600">メールアドレス</label>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                       class="rc-input">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm text-slate-600">パスワード</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="rc-input">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between text-sm text-slate-500">
                <label for="remember_me" class="inline-flex items-center space-x-2">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" name="remember">
                    <span>ログイン状態を保持する</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-emerald-600 hover:text-emerald-500" href="{{ route('password.request') }}">
                        パスワードをお忘れですか？
                    </a>
                @endif
            </div>

            <div class="space-y-3">
                <button type="submit" class="w-full rounded-xl bg-emerald-500 text-white font-semibold py-3 hover:bg-emerald-400 transition">
                    ログイン
                </button>
                <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center rounded-xl border border-emerald-500 py-3 text-emerald-600 hover:bg-emerald-50 transition">
                    アカウントを登録する
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
