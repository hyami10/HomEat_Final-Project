<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 1. WELCOME BOX -->
            @auth
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    Welcome back, <span class="text-green-600 dark:text-green-400 font-bold">{{ Auth::user()->name }}</span> !
                </div>
            </div>

            @if(is_null(Auth::user()->email_verified_at))
            <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 text-yellow-900 dark:text-yellow-200 rounded-lg shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 space-y-2">
                    <p class="font-semibold dark:text-yellow-100">Akun Anda menunggu verifikasi admin.</p>
                    <p class="text-sm dark:text-yellow-200/90">Anda tetap bisa menjelajah menu, namun proses checkout akan diblokir sampai admin menyetujui pendaftaran Anda. Mohon pantau halaman <a href="{{ route('orders.track') }}" class="underline font-semibold hover:text-yellow-700 dark:hover:text-yellow-100">Tracking Order</a> atau hubungi admin bila butuh bantuan.</p>
                </div>
            </div>
            @endif
            @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-lg">Welcome to <span class="text-green-600 dark:text-green-400 font-bold">HomEat</span>!</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Browse our menu below. <a href="{{ route('login') }}" class="text-green-600 dark:text-green-400 hover:underline font-semibold">Login</a> or <a href="{{ route('register') }}" class="text-green-600 dark:text-green-400 hover:underline font-semibold">Sign Up</a> to add items to cart and place orders.</p>
                </div>
            </div>
            @endauth

            <!-- 2. SEARCH BAR -->
            <div class="mb-8">
                <form action="{{ route('dashboard') }}" method="GET" class="flex gap-2">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari menu lezat Anda..." 
                           value="{{ request('search') }}"
                           class="w-full max-w-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm"
                    >
                    <button type="submit" class="bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white font-bold py-2 px-4 rounded shadow">
                        SEARCH
                    </button>
                </form>
            </div>

            <!-- 3. JUDUL & TOMBOL ADMIN -->
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100 leading-none">
                    Our Menu
                </h3>

                @auth
                    @if(Auth::user()->role === 'admin')
                        <div class="mt-2">
                            <a href="{{ route('foods.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow inline-flex items-center text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                KELOLA MENU
                            </a>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- 4. GRID MAKANAN -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($foods as $food)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-gray-100 dark:border-gray-700">
                        
                        <!-- Gambar Makanan -->
                        <div class="h-48 bg-gray-200 dark:bg-gray-700 overflow-hidden relative">
                             @if($food->image)
                                <img src="{{ $food->image_url }}" alt="{{ $food->name }}" class="w-full h-full object-cover" loading="lazy">
                             @else
                                <div class="flex items-center justify-center h-full text-gray-400 dark:text-gray-500">
                                    <span>No Image</span>
                                </div>
                             @endif
                        </div>

                        <!-- Info Makanan -->
                        <div class="p-5">
                            <h4 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-1 capitalize">{{ $food->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                {{ $food->category ?? 'Main Course' }} | Stok: {{ $food->stock ?? '-' }}
                            </p>

                            <div class="flex justify-between items-center mt-4">
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    Rp {{ number_format($food->price, 0, ',', '.') }}
                                </span>
                                
                                <a href="{{ route('menu.show', $food->id) }}" class="bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white text-sm font-bold py-2 px-4 rounded transition">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-3 text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <p class="text-red-500 dark:text-red-400 text-lg font-semibold">Menu tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>
            
        </div>
    </div>
</x-app-layout>