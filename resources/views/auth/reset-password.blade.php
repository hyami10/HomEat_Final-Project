<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Your Password</h2>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-400 font-medium">Enter your new password below to regain access to your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
            <div class="mt-1 relative">
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email', $request->email) }}" 
                       required 
                       autofocus 
                       autocomplete="username"
                       readonly
                       class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl !text-black placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#74c374] focus:border-transparent transition dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                       placeholder="your@email.com">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                </svg>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
            <div class="mt-1 relative">
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="new-password"
                       class="block w-full px-4 py-3 pr-10 bg-white border border-gray-300 rounded-xl !text-black placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#74c374] focus:border-transparent transition dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                       placeholder="Enter strong password">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <p class="mt-1 text-xs text-gray-500">Use at least 8 characters with letters, numbers & symbols</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-5">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
            <div class="mt-1 relative">
                <input id="password_confirmation" 
                       type="password" 
                       name="password_confirmation" 
                       required 
                       autocomplete="new-password"
                       class="block w-full px-4 py-3 pr-10 bg-white border border-gray-300 rounded-xl !text-black placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#74c374] focus:border-transparent transition dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                       placeholder="Re-enter password">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Reset Button -->
        <div class="mt-6">
            <button type="submit" class="w-full bg-[#74c374] hover:bg-[#5fa85f] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-green-200 transition transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#74c374] focus:ring-offset-2">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Reset Password
                </span>
            </button>
        </div>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-[#74c374] transition dark:text-gray-400 dark:hover:text-[#74c374]">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to login
                </span>
            </a>
        </div>
    </form>
</x-guest-layout>
