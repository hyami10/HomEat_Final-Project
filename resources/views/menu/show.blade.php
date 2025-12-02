<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Menu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Tombol Kembali --}}
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-green-600 hover:text-green-800 font-bold inline-flex items-center transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Kembali ke Menu
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg flex flex-col md:flex-row">
                
                {{-- BAGIAN KIRI: GAMBAR --}}
                <div class="w-full md:w-1/2 h-64 md:h-[500px] relative bg-gray-100">
                    @if($food->image)
                        <img src="{{ $food->image_url }}" alt="{{ $food->name }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="flex items-center justify-center h-full text-gray-400">
                            <span class="text-lg font-semibold">No Image Available</span>
                        </div>
                    @endif
                </div>

                {{-- BAGIAN KANAN: INFO DETAIl --}}
                <div class="w-full md:w-1/2 p-8 flex flex-col justify-center">
                    
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 capitalize">{{ $food->name }}</h1>
                    <p class="text-2xl font-bold text-[#74c374] mb-4">Rp {{ number_format($food->price, 0, ',', '.') }}</p>
                    
                    <div class="prose text-gray-600 dark:text-gray-300 mb-6">
                        <p>{{ $food->description ?? 'Tidak ada deskripsi.' }}</p>
                    </div>

                    <div class="mb-6 text-sm text-gray-500">
                        <p class="mb-1"><span class="font-bold text-gray-700">Kategori:</span> {{ $food->category }}</p>
                        <p>
                            <span class="font-bold text-gray-700">Stok Tersedia:</span> 
                            @if($food->stock > 5)
                                <span class="text-green-600 font-bold">{{ $food->stock }} porsi</span>
                            @elseif($food->stock > 0)
                                <span class="text-red-500 font-bold">Sisa {{ $food->stock }} porsi! (Buruan)</span>
                            @else
                                <span class="text-gray-400 font-bold">Habis</span>
                            @endif
                        </p>
                    </div>

                    {{-- FORM TAMBAH KE KERANJANG --}}
                    <div class="mt-auto">
                        @if($food->stock > 0)
                            @auth
                                <form action="{{ route('carts.store', $food->id) }}" method="POST" id="addToCartForm">
                                    @csrf
                                    <div class="flex gap-4">
                                        <input type="number" name="quantity" value="1" min="1" max="{{ $food->stock }}" 
                                               class="w-20 text-center border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm">
                                        
                                        <button type="submit" class="flex-1 bg-[#74c374] hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow transition transform hover:scale-105">
                                            Tambah ke Keranjang
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                    <p class="text-sm text-yellow-700 mb-3">Anda harus login untuk menambahkan item ke keranjang.</p>
                                    <div class="flex gap-2">
                                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="bg-[#74c374] hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg text-sm transition">
                                            Login
                                        </a>
                                        <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition">
                                            Daftar
                                        </a>
                                    </div>
                                </div>
                            @endauth
                        @else
                            <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 px-6 rounded-lg cursor-not-allowed">
                                Stok Habis
                            </button>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="toastNotification" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <div>
                <p class="font-bold" id="toastMessage">Item berhasil ditambahkan ke keranjang!</p>
                <div class="flex gap-2 mt-2">
                    <a href="{{ route('carts.index') }}" class="text-sm underline hover:no-underline">Lihat Keranjang</a>
                    <span class="text-sm">|</span>
                    <button id="continueShoppingBtn" class="text-sm underline hover:no-underline">Lanjut Belanja</button>
                </div>
            </div>
            <button id="closeToastBtn" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <script @nonce>
        // Handle add to cart form submission with AJAX
        document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Disable button during submission
            submitButton.disabled = true;
            submitButton.textContent = 'Menambahkan...';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    // Reload page to update cart badge
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + (data.message || 'Gagal menambahkan ke keranjang'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback to normal form submission
                form.submit();
            });
        });

        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const messageEl = document.getElementById('toastMessage');
            if (messageEl) messageEl.textContent = message || 'Item berhasil ditambahkan ke keranjang!';
            toast.classList.remove('hidden');
            setTimeout(() => {
                closeToast();
            }, 5000);
        }

        function closeToast() {
            const toast = document.getElementById('toastNotification');
            toast.classList.add('hidden');
        }
        
        // Attach event listeners for toast buttons
        document.getElementById('continueShoppingBtn')?.addEventListener('click', closeToast);
        document.getElementById('closeToastBtn')?.addEventListener('click', closeToast);

        // Show toast if success message exists from redirect
        @if(session('success'))
            showToast('{{ session('success') }}');
        @endif
    </script>

    <style @nonce>
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    </style>
</x-app-layout>