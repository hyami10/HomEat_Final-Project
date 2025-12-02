<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kelola Menu Makanan') }}
            </h2>
            {{-- Tombol Tambah Data --}}
            <a href="{{ route('foods.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                + Tambah Makanan
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Tabel Mulai Disini --}}
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Gambar</th>
                                    <th scope="col" class="px-6 py-3">Nama Makanan</th>
                                    <th scope="col" class="px-6 py-3">Harga</th>
                                    <th scope="col" class="px-6 py-3">Stok</th>
                                    <th scope="col" class="px-6 py-3">Kategori</th>
                                    <th scope="col" class="px-6 py-3">Deskripsi</th> {{-- KOLOM BARU --}}
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Looping Data Makanan --}}
                                @forelse($foods as $food)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">
                                        {{-- Cek ada gambar atau tidak --}}
                                        @if($food->image)
                                            <img src="{{ $food->image_url }}" class="w-16 h-16 object-cover rounded" loading="lazy" alt="{{ $food->name }}">
                                        @else
                                            <span class="text-gray-400">No Image</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $food->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rp {{ number_format($food->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $food->stock }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                            {{ $food->category }}
                                        </span>
                                    </td>
                                    {{-- ISI DESKRIPSI BARU --}}
                                    <td class="px-6 py-4 text-gray-500">
                                        {{ Str::limit($food->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 flex gap-2">
                                        <a href="{{ route('foods.edit', $food) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                        <form action="{{ route('foods.destroy', $food) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                {{-- Tampilan kalau data kosong --}}
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Belum ada data makanan. Silakan tambah baru.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>