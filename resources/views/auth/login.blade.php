<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-100 p-3 rounded">
                {{ session('status') }}
        </div>
        @endif

        <h2>Welcome Back!</h2>
        <p class="subtitle">
            Glad to see you again!<br />
            With HomEat, your comfort food is only a few taps away — let's get you started.
        </p>

        <label>Email</label>
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="example@email.com"
            required
            autofocus
            autocomplete="username"
        />

        @error('email')
            <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
        @enderror

        <label>Password</label>
        <input
                            type="password"
                            name="password"
            placeholder="At least 8 characters"
            required
            autocomplete="current-password"
        />

        @error('password')
            <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
        @enderror

        <div class="forgot">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Forgot Password?</a>
            @endif
        </div>

        <div class="remember-me" style="text-align: left; margin-bottom: 15px;">
            <label style="font-size: 12px; color: #333;">
                <input type="checkbox" name="remember" style="margin-right: 5px;"> Remember me
            </label>
        </div>

        <button type="submit">Sign In</button>

        <div class="signup">
            Don't have an account? <a href="{{ route('register') }}">Sign up Here</a>
        </div>
    </form>
</x-guest-layout>
