<section x-data="{ editing: {{ $errors->has('alamat') || $errors->has('notelp') ? 'true' : 'false' }} }">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Address & Phone Number') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Add your delivery address and contact number.") }}
        </p>
    </header>

    <!-- View Mode -->
    <div x-show="!editing" class="mt-6 space-y-6">
        <div>
            <x-input-label :value="__('Alamat')" />
            <div class="mt-1 text-gray-900 dark:text-gray-200 p-2 bg-gray-50 dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-700">
                {{ $user->alamat ?? 'Belum diisi' }}
            </div>
        </div>

        <div>
            <x-input-label :value="__('No. Telepon')" />
            <div class="mt-1 text-gray-900 dark:text-gray-200 p-2 bg-gray-50 dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-700">
                {{ $user->notelp ? preg_replace('/(\d{4})(\d{4})(\d{0,4})/', '$1-$2-$3', $user->notelp) : 'Belum diisi' }}
            </div>
        </div>

        <x-primary-button type="button" x-on:click="editing = true">
            {{ __('Edit Details') }}
        </x-primary-button>
    </div>

    <!-- Edit Mode -->
    <form x-show="editing" method="post" action="{{ route('profile.updateAlamatNotelp') }}" class="mt-6 space-y-6" style="display: none;">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="alamat" :value="__('Alamat')" />
            <x-text-input id="alamat" name="alamat" type="text" class="mt-1 block w-full" :value="old('alamat', $user->alamat)" required placeholder="Contoh: Jl. Mawar No. 12" />
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
        </div>

        <div>
            <x-input-label for="notelp" :value="__('No. Telepon')" />
            <x-text-input id="notelp" name="notelp" type="text" class="mt-1 block w-full phone-input" :value="old('notelp', $user->notelp)" required maxlength="13" placeholder="081234567890" />
            <x-input-error class="mt-2" :messages="$errors->get('notelp')" />
            <p class="text-sm text-gray-500 mt-1">Hanya angka, maksimal 13 digit.</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            
            <button type="button" x-on:click="editing = false" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 underline">
                {{ __('Cancel') }}
            </button>

            @if (session('status') === 'address-updated')
                <p x-data="flashMessage" x-show="show" x-transition class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>