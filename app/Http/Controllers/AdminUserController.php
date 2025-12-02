<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', 'in:verified,unverified'],
        ]);

        $query = User::query()->orderByRaw('email_verified_at IS NULL DESC')->orderByDesc('created_at');

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->paginate(10)->withQueryString();
        $pendingCount = User::whereNull('email_verified_at')->count();
        $recentPendingUsers = User::whereNull('email_verified_at')
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'created_at']);

        return view('admin.users.index', compact('users', 'pendingCount', 'recentPendingUsers'));
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat mengubah status verifikasi akun sendiri.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Status admin tidak dapat diubah.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
                
                if ($lockedUser->email_verified_at) {
                    throw new \Exception('Pengguna sudah diverifikasi.');
                }

                $lockedUser->email_verified_at = now();
                $lockedUser->save();
            });

            return back()->with('success', 'Email pengguna berhasil diverifikasi.');
        } catch (\Exception $e) {
            return back()->with('info', $e->getMessage());
        }
    }
}
