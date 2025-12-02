<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Pesanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Pesan Sukses --}}
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-4 rounded shadow-sm" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Daftar Pesanan Masuk</h3>
                    <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 text-xs font-semibold px-2.5 py-0.5 rounded">Total: {{ $orders->count() }} Pesanan</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs font-bold leading-normal">
                            <tr>
                                <th class="py-3 px-6 text-left">Order ID</th>
                                <th class="py-3 px-6 text-left">Customer</th>
                                <th class="py-3 px-6 text-center">Pembayaran</th>
                                <th class="py-3 px-6 text-center">Total</th>
                                <th class="py-3 px-6 text-center">Status Saat Ini</th>
                                <th class="py-3 px-6 text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 dark:text-gray-300 text-sm font-light divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($orders as $order)
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                                <td class="py-4 px-6 text-left whitespace-nowrap">
                                    <span class="font-bold text-gray-800 dark:text-gray-100">#{{ $order->id }}</span> <br>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                </td>
                                <td class="py-4 px-6 text-left">
                                    <div class="flex items-center">
                                        <span class="font-bold text-gray-700 dark:text-gray-200">{{ $order->user->name }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-[150px] truncate" title="{{ $order->shipping_address }}">
                                        {{ $order->shipping_address }}
                                    </div>
                                    {{-- Detail Items --}}
                                    @if($order->items->count() > 0)
                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-600 pt-2">
                                            @foreach($order->items as $item)
                                                <div>{{ $item->food_name }} x{{ $item->quantity }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($order->payment_method == 'Transfer')
                                        <div class="flex flex-col items-center">
                                            <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 py-1 px-3 rounded-full text-xs font-bold mb-1">
                                                Transfer Bank
                                            </span>
                                            @if($order->payment_proof)
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300">
                                                    📷 Lihat Bukti
                                                </a>
                                            @else
                                                <span class="text-[10px] text-red-500 dark:text-red-400 italic">Belum ada bukti</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 py-1 px-3 rounded-full text-xs font-bold">
                                            COD
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-green-600 dark:text-green-400">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @php
                                        $statusColor = match($order->status) {
                                            'pending' => 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300',
                                            'cooking' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                            'delivery' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300',
                                            'completed' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                            'cancelled' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
                                        };
                                        $statusLabel = match($order->status) {
                                            'pending' => 'Menunggu',
                                            'cooking' => 'Dimasak',
                                            'delivery' => 'Diantar',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => ucfirst($order->status)
                                        };
                                    @endphp
                                    <span class="{{ $statusColor }} py-1 px-3 rounded-full text-xs font-bold shadow-sm">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        {{-- UPDATE STATUS --}}
                                        <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="w-full">
                                            @csrf @method('PATCH')

                                            @if($order->status == 'pending')
                                                @if($order->payment_method == 'Transfer' && !$order->payment_proof)
                                                    <span class="text-gray-400 dark:text-gray-500 text-xs font-bold italic flex items-center justify-center gap-1">⏳ Menunggu Bukti</span>
                                                @else
                                                    <button type="submit" name="status" value="cooking" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-1.5 px-3 rounded shadow text-xs font-bold transition transform hover:scale-105 flex items-center justify-center gap-1">
                                                        <span>🍳</span> {{ $order->payment_method == 'Transfer' ? 'Verifikasi & Masak' : 'Terima & Masak' }}
                                                    </button>
                                                @endif
                                            @elseif($order->status == 'cooking')
                                                <button type="submit" name="status" value="delivery" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-1.5 px-3 rounded shadow text-xs font-bold transition transform hover:scale-105 flex items-center justify-center gap-1">
                                                    <span>🛵</span> Antar Pesanan
                                                </button>
                                            @elseif($order->status == 'delivery')
                                                <span class="text-orange-500 dark:text-orange-400 text-xs font-bold italic flex items-center gap-1">⏳ Menunggu Customer...</span>
                                            @elseif($order->status == 'completed')
                                                <span class="text-green-600 dark:text-green-400 text-xs font-bold flex items-center gap-1">✅ Selesai</span>
                                            @elseif($order->status == 'cancelled')
                                                <span class="text-red-500 dark:text-red-400 text-xs font-bold">❌ Dibatalkan</span>
                                            @endif
                                        </form>

                                        {{-- TOLAK PESANAN (Hanya saat Pending) --}}
                                        @if($order->status == 'pending')
                                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST" class="w-full reject-form" data-order-id="{{ $order->id }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="reason" id="reason_input_{{ $order->id }}">
                                                <button type="button" class="reject-btn w-full bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 py-1.5 px-3 rounded border border-red-200 dark:border-red-700 text-xs font-bold transition">
                                                    ❌ Tolak Pesanan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script @nonce>
        document.addEventListener('DOMContentLoaded', function() {
            // Use event delegation for better performance and dynamic content support
            document.body.addEventListener('click', function(e) {
                if (e.target.closest('.reject-btn')) {
                    const form = e.target.closest('.reject-form');
                    const orderId = form.dataset.orderId;
                    
                    let reason = prompt("Masukkan alasan penolakan (Contoh: Bahan habis/Resto tutup):");
                    
                    // Allow empty reason, only cancel if user pressed Cancel (null)
                    if (reason !== null) {
                        document.getElementById('reason_input_' + orderId).value = reason;
                        form.submit();
                    }
                }
            });
        });
    </script>
</x-app-layout>