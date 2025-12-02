<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tracking Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('order_success'))
            <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-6 relative" role="alert">
                <p class="font-bold">Pembayaran Berhasil!</p>
                <p>Pesanan Anda sedang diteruskan ke pihak restoran.</p>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" id="close-success-alert">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-200 p-4 mb-6" role="alert">
                <p class="font-bold">Perhatian</p>
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Riwayat Pesanan</h3>

                    @forelse($orders as $order)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 mb-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="font-bold text-lg">Order #{{ $order->id }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    <div class="mt-1">
                                        <span class="text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">
                                            Bayar: {{ $order->payment_method }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end gap-2">
                                    {{-- BADGE STATUS --}}
                                    <span class="px-3 py-1 rounded-full text-sm font-bold 
                                        {{ $order->status == 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : '' }}
                                        {{ $order->status == 'cooking' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : '' }}
                                        {{ $order->status == 'delivery' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300' : '' }}
                                        {{ $order->status == 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
                                        {{ $order->status == 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : '' }}">
                                        {{ match($order->status) {
                                            'pending' => 'Menunggu Konfirmasi',
                                            'cooking' => 'Sedang Dimasak',
                                            'delivery' => 'Sedang Diantar',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => ucfirst($order->status)
                                        } }}
                                    </span>

                                    {{-- TOMBOL USER: TERIMA PESANAN --}}
                                    @if($order->status == 'delivery')
                                        <form action="{{ route('orders.complete', $order->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-2 px-4 rounded shadow-lg transition transform hover:scale-105">
                                                ✅ Pesanan Diterima
                                            </button>
                                        </form>
                                    @endif

                                    {{-- TOMBOL USER: BATALKAN PESANAN --}}
                                    @if($order->status == 'pending')
                                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
                                                ❌ Batalkan Pesanan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- UPLOAD BUKTI PEMBAYARAN (Transfer Only) --}}
                            @if($order->payment_method == 'Transfer' && $order->status == 'pending')
                                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-lg">
                                    <h4 class="font-bold text-blue-800 dark:text-blue-300 text-sm mb-2">Instruksi Pembayaran Transfer</h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Silakan transfer ke <strong class="dark:text-gray-200">BCA 123-456-7890</strong> a.n HomeEat Official, lalu unggah bukti transfer di bawah ini agar pesanan diproses.</p>

                                    @if($order->payment_proof)
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 dark:text-green-400 text-sm font-bold flex items-center">
                                                ✅ Bukti Terkirim
                                            </span>
                                            <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300">Lihat Bukti</a>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 italic">(Menunggu verifikasi admin)</span>
                                        </div>
                                    @else
                                        <form action="{{ route('orders.uploadProof', $order->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                            @csrf @method('PATCH')
                                            <input type="file" name="payment_proof" class="text-xs text-gray-500 dark:text-gray-400 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 dark:file:bg-blue-800 file:text-blue-700 dark:file:text-blue-200 hover:file:bg-blue-200 dark:hover:file:bg-blue-700" required accept="image/jpeg,image/png,image/jpg">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-1 px-3 rounded shadow transition">
                                                📤 Unggah Bukti
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-2 mt-2">Dikirim ke: {{ $order->shipping_address }}</p>
                            
                            {{-- DETAIL ITEMS PESANAN --}}
                            @if($order->items->count() > 0)
                                <div class="mt-3 mb-3 border-t border-gray-200 dark:border-gray-600 pt-3">
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Detail Pesanan:</p>
                                    <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                                <span>{{ $item->food_name }} x {{ $item->quantity }}</span>
                                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <p class="font-bold text-green-600 dark:text-green-400">Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center">Belum ada pesanan aktif.</p>
                    @endforelse

                </div>
            </div>
        </div>
    </div>
    </div>

    <script @nonce>
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.getElementById('close-success-alert');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            }
        });
    </script>
</x-app-layout>