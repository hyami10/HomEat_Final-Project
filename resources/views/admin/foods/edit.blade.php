<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Makanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Form Edit --}}
                    <form action="{{ route('foods.update', $food) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') 

                        {{-- Nama Makanan --}}
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Makanan</label>
                            <input type="text" name="name" id="name" value="{{ $food->name }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            {{-- Harga --}}
                            <div class="mb-4">
                                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga (Rp)</label>
                                <input type="number" name="price" id="price" value="{{ $food->price }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600">
                            </div>

                            {{-- Stok --}}
                            <div class="mb-4">
                                <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok</label>
                                <input type="number" name="stock" id="stock" value="{{ $food->stock }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600">
                            </div>
                        </div>

                        {{-- Kategori --}}
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select name="category" id="category" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600">
                                <option value="Main Course" {{ $food->category == 'Main Course' ? 'selected' : '' }}>Main Course</option>
                                <option value="Snack" {{ $food->category == 'Snack' ? 'selected' : '' }}>Snack</option>
                                <option value="Drink" {{ $food->category == 'Drink' ? 'selected' : '' }}>Drink</option>
                            </select>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi Makanan</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600">{{ old('description', $food->description) }}</textarea>
                        </div>

                        {{-- Ganti Gambar --}}
                        <div class="mb-6">
                            <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ganti Foto (Kosongkan jika tidak ingin ganti)</label>
                            
                            {{-- Tampilkan foto lama --}}
                            @if($food->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $food->image) }}" class="w-20 h-20 object-cover rounded">
                                </div>
                            @endif

                            <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Update Makanan
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>