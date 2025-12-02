<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information, address, and contact details.") }}
        </p>
    </header>

    {{-- Success Messages --}}
    @if (session('status') === 'profile-updated' || session('status') === 'address-updated')
        <div class="mt-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md">
            ✓ {{ __('Profile berhasil diperbarui!') }}
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')



        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" readonly required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="alamat" :value="__('Alamat')" />
            <x-text-input id="alamat" name="alamat" type="text" class="mt-1 block w-full" :value="old('alamat', $user->alamat)" placeholder="Contoh: Jl. Mawar No. 12, Jakarta Selatan" />
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
            <p class="text-xs text-gray-500 mt-1">Alamat untuk pengiriman.</p>
        </div>

        <div>
            <x-input-label for="notelp" :value="__('No. Telepon')" />
            <x-text-input id="notelp" name="notelp" type="text" class="mt-1 block w-full phone-input" :value="old('notelp', $user->notelp)" maxlength="13" placeholder="081234567890" />
            <x-input-error class="mt-2" :messages="$errors->get('notelp')" />
            <p class="text-xs text-gray-500 mt-1">Hanya angka, 10-13 digit.</p>
        </div>

        <div>
            <x-input-label for="profile_photo" :value="__('Profile Photo')" />
            <div class="mt-2 flex items-center gap-4">
                @php
                    $photoUrl = $user->profile_photo ? asset('storage/' . $user->profile_photo) : null;
                @endphp

                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ __('Current profile photo') }}" class="w-16 h-16 rounded-full object-cover border border-gray-200" loading="lazy">
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-semibold text-gray-600 dark:text-gray-200">
                        {{ \Illuminate\Support\Str::of($user->name)->substr(0, 2)->upper() }}
                    </div>
                @endif

                <div class="space-y-2">
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/png,image/jpeg" class="block w-full text-sm text-gray-700 dark:text-gray-300" />
                    <p class="text-xs text-gray-500">JPG/PNG maks 2MB.</p>

                    @if ($user->profile_photo)
                        <label class="flex items-center gap-2 text-sm text-red-600">
                            <input type="checkbox" name="remove_profile_photo" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            {{ __('Hapus foto saat ini') }}
                        </label>
                    @endif
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="flashMessage"
                    x-show="show"
                    x-transition
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
