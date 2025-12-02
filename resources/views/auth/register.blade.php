<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <h2>Join HomEat!</h2>
        <p class="subtitle">
            Join us and discover local homemade meals crafted with passion.
        </p>

        <label>Full Name</label>
        <input
            type="text"
            name="name"
            value="{{ old('name') }}"
            placeholder="Enter your full name"
            required
            autofocus
            autocomplete="name"
        />

        @error('name')
            <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
        @enderror

        <label>Email</label>
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="example@email.com"
            required
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
            autocomplete="new-password"
        />

        @error('password')
            <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
        @enderror

        <label>Confirm Password</label>
        <input
                            type="password"
            name="password_confirmation"
            placeholder="Confirm your password"
            required
            autocomplete="new-password"
        />

        @error('password_confirmation')
            <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
        @enderror

        <button type="submit">Sign Up</button>

        <div class="signup">
            Already have an account? <a href="{{ route('login') }}">Sign in Here</a>
        </div>
    </form>
</x-guest-layout>
