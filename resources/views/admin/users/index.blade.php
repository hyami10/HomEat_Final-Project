<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Verifikasi Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Pantau status verifikasi email dan pastikan hanya pengguna tervalidasi yang dapat bertransaksi.</p>
                        </div>
                        <form method="GET" class="flex items-center gap-2">
                            <label for="status" class="text-sm font-medium">Filter</label>
                            <select id="status" name="status" class="border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="">Semua</option>
                                <option value="unverified" @selected(request('status') === 'unverified')>Belum Diverifikasi</option>
                                <option value="verified" @selected(request('status') === 'verified')>Sudah Diverifikasi</option>
                            </select>
                            <button type="submit" class="bg-green-600 text-white text-sm font-semibold px-3 py-2 rounded-md hover:bg-green-700">Terapkan</button>
                        </form>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-sm text-yellow-900 space-y-2">
                        @if ($pendingCount > 0)
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <p><strong>{{ $pendingCount }}</strong> akun pelanggan menunggu verifikasi admin.</p>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="status" value="unverified">
                                    <button type="submit" class="text-yellow-800 font-semibold underline">Lihat daftar penuh</button>
                                </form>
                            </div>
                            @if ($recentPendingUsers->isNotEmpty())
                                <div class="space-y-1">
                                    <p class="text-xs uppercase tracking-wide text-yellow-700">Pendaftar terbaru</p>
                                    <ul class="divide-y divide-yellow-200 text-sm">
                                        @foreach ($recentPendingUsers as $pending)
                                            <li class="py-1 flex justify-between items-center">
                                                <span class="font-medium">{{ $pending->name }} <span class="text-gray-500 text-xs">({{ $pending->email }})</span></span>
                                                <span class="text-xs text-gray-500">{{ $pending->created_at->diffForHumans() }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @else
                            <p class="text-sm">Tidak ada akun yang menunggu verifikasi. 🎉</p>
                        @endif
                    </div>

                    @if (session('success'))
                        <div class="p-3 bg-green-100 text-green-700 rounded-md text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="p-3 bg-red-100 text-red-700 rounded-md text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="p-3 bg-blue-100 text-blue-700 rounded-md text-sm">
                            {{ session('info') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Pengguna</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Peran</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Status Email</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Tanggal Daftar</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                            <div class="text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-4 py-3 capitalize">{{ $user->role ?? 'customer' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($user->email_verified_at)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Terverifikasi</span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $user->email_verified_at->translatedFormat('d F Y H:i') }}</p>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Menunggu</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $user->created_at?->translatedFormat('d F Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if (!$user->email_verified_at && $user->role !== 'admin')
                                                <form method="POST" action="{{ route('admin.users.verify', $user) }}" onsubmit="return confirm('Verifikasi email pengguna ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-md">Verifikasi</button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-sm">Tidak ada aksi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                            Tidak ada pengguna ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
