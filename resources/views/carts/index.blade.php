<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Keranjang Belanja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- KOLOM KIRI: Daftar Barang --}}
                <div class="lg:col-span-2">
                    @forelse($cartItems as $item)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 flex items-center p-4 relative border border-gray-100">
                        
                        {{-- Gambar (LOGIC FIX) --}}
                        <div class="w-24 h-24 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->food->image)
                                @if(Str::startsWith($item->food->image, 'images/'))
                                    <img src="{{ asset($item->food->image) }}" class="w-full h-full object-cover">
                                @else
                                    <img src="{{ asset('storage/' . $item->food->image) }}" class="w-full h-full object-cover">
                                @endif
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400 text-xs">No Image</div>
                            @endif
                        </div>

                        {{-- Info Barang --}}
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $item->food->name }}</h3>
                            <p class="text-gray-500 text-sm">{{ $item->food->category }}</p>
                            <p class="text-[#74c374] font-bold mt-1">Rp {{ number_format($item->food->price, 0, ',', '.') }}</p>
                        </div>

                        {{-- Quantity Control --}}
                        <div class="text-right mr-8"> 
                            <div class="flex items-center justify-end gap-2 mb-2">
                                <form action="{{ route('carts.update', $item) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="type" value="minus">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 font-bold text-gray-600 flex items-center justify-center">-</button>
                                </form>
                                <span class="text-sm font-semibold w-6 text-center text-gray-900 dark:text-white">{{ $item->quantity }}</span>
                                <form action="{{ route('carts.update', $item) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="type" value="plus">
                                    <button type="submit" {{ $item->quantity >= $item->food->stock ? 'disabled' : '' }} class="w-8 h-8 rounded-full bg-[#74c374] text-white hover:bg-green-600 font-bold flex items-center justify-center disabled:opacity-50">+</button>
                                </form>
                            </div>
                            <p class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->food->price * $item->quantity, 0, ',', '.') }}</p>
                        </div>

                        {{-- Delete --}}
                        <form action="{{ route('carts.destroy', $item) }}" method="POST" class="absolute top-4 right-4 z-10">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition p-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="p-10 bg-white dark:bg-gray-800 rounded-lg text-center border border-dashed border-gray-300">
                        <p class="text-gray-500 mb-2">Keranjang kosong.</p>
                        <a href="{{ route('dashboard') }}" class="text-[#74c374] font-bold hover:underline">Mulai Belanja &rarr;</a>
                    </div>
                    @endforelse
                </div>

                {{-- KOLOM KANAN: Checkout --}}
                @if($cartItems->count() > 0)
                <div class="lg:col-span-1">
                    
                    <!-- BOX ALAMAT -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Alamat Pengiriman
                        </h3>
                        
                        @if(Auth::user()->alamat)
                            <div class="mb-4 bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-800 font-bold text-sm">{{ Auth::user()->name }}</p>
                                <p class="text-gray-600 text-sm mt-1">{{ Auth::user()->alamat }}</p>
                                <p class="text-gray-500 text-xs mt-1">{{ Auth::user()->notelp }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="text-sm text-[#74c374] font-bold hover:underline">Ubah Alamat</a>
                        @else
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <p class="text-sm text-yellow-700">Kamu belum mengatur alamat.</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block w-full text-center bg-gray-800 text-white py-2 rounded hover:bg-gray-700 text-sm">Atur Alamat</a>
                        @endif
                    </div>

                    <!-- RINGKASAN PEMBAYARAN -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 sticky top-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Ringkasan Pesanan</h3>
                        
                        @php
                            $subtotal = $cartItems->sum(function($item) { return $item->food->price * $item->quantity; });
                            $tax = $subtotal * 0.11;
                            $delivery = 10000;
                            $total = $subtotal + $tax + $delivery;
                        @endphp

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-gray-600 text-sm">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 text-sm">
                                <span>Pajak (11%)</span>
                                <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 text-sm">
                                <span>Ongkos Kirim</span>
                                <span>Rp {{ number_format($delivery, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-gray-300 my-4 pt-4">
                            <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white">
                                <span>Total</span>
                                <span class="text-[#74c374]">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- LOGIKA TOMBOL CHECKOUT --}}
                        @if(Auth::user()->role === 'admin')
                            {{-- BLOKIR ADMIN DI TAMPILAN --}}
                            <div class="bg-red-100 text-red-700 p-3 rounded text-center text-sm font-bold">
                                Admin Tidak Bisa Belanja
                            </div>
                        @elseif(Auth::user()->alamat)
                            <button data-checkout-button class="w-full bg-[#74c374] hover:bg-green-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-green-200 transition transform hover:-translate-y-1">
                                Lanjut Pembayaran
                            </button>
                        @else
                            <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-xl cursor-not-allowed">
                                Lengkapi Alamat Dulu
                            </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL FIXED STRUCTURE --}}
    {{-- MODAL FIXED STRUCTURE --}}
    <div id="checkoutModal" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" data-backdrop-close></div>
      
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    {{-- Header Modal --}}
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="text-center sm:text-left">
                            <h3 class="text-xl font-bold leading-6 text-gray-900 mb-6" id="modal-title">Pilih Metode Pembayaran</h3>
                            
                            <form action="{{ route('orders.checkout') }}" method="POST" id="checkoutForm">
                                @csrf
                                
                                <div class="space-y-4">
                                    {{-- OPSI 1: COD --}}
                                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-green-500 peer-checked:border-green-600 peer-checked:ring-1 peer-checked:ring-green-600 transition-all group">
                                        <input type="radio" name="payment_method" value="COD" class="sr-only peer" checked>
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span class="block text-sm font-bold text-gray-900 mb-1 group-hover:text-green-600">Cash On Delivery (COD)</span>
                                                <span class="block text-xs text-gray-500">Bayar tunai ke kurir saat makanan sampai.</span>
                                            </span>
                                        </span>
                                        <svg class="h-5 w-5 text-gray-400 peer-checked:text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-green-500" aria-hidden="true"></span>
                                    </label>
        
                                    {{-- OPSI 2: TRANSFER --}}
                                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-green-500 transition-all group">
                                        <input type="radio" name="payment_method" value="Transfer" class="sr-only peer">
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span class="block text-sm font-bold text-gray-900 mb-1 group-hover:text-green-600">Transfer Bank (Manual Check)</span>
                                                <span class="block text-xs text-gray-500 mb-2">Transfer ke salah satu rekening di bawah:</span>
                                                
                                                <div class="bg-gray-50 p-2 rounded text-xs text-gray-700 border border-gray-200">
                                                    <div class="flex justify-between mb-1">
                                                        <span>BCA:</span>
                                                        <span class="font-mono font-bold">123-456-7890</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Mandiri:</span>
                                                        <span class="font-mono font-bold">000-111-222-333</span>
                                                    </div>
                                                    <div class="mt-1 text-[10px] text-gray-400 italic">a.n HomeEat Official</div>
                                                </div>
                                            </span>
                                        </span>
                                        <svg class="h-5 w-5 text-gray-400 peer-checked:text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-green-500" aria-hidden="true"></span>
                                    </label>
                                </div>

                                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                    <button type="button" data-close-modal class="w-full sm:w-auto inline-flex justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Batal</button>
                                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg bg-[#74c374] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                        Bayar Sekarang - Rp {{ number_format($total ?? 0, 0, ',', '.') }}
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script @nonce>
        // Checkout modal - CSP compliant with event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('checkoutModal');
            const openBtn = document.querySelector('[data-checkout-button]');
            const closeBtn = document.querySelector('[data-close-modal]');
            const backdrop = document.querySelector('[data-backdrop-close]');
            
            // Open modal
            if (openBtn) {
                openBtn.addEventListener('click', function() {
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        console.error('Checkout modal not found!');
                    }
                });
            }
            
            // Close modal - button
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    if (modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
            
            // Close modal - backdrop
            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    if (modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
    </script>
</x-app-layout>