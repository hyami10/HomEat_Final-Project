<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminAccountManager
{
    public function sync(): void
    {
        $this->syncAdmin();
        $this->syncDefaultUser();
    }

    public function syncAdmin(): User
    {
        $config = Config::get('admin.seed', []);
        $password = Arr::get($config, 'password');

        if (blank($password)) {
            throw new \RuntimeException('ADMIN_PASSWORD (or ADMIN_DEFAULT_PASSWORD) must be set in the environment.');
        }

        $admin = User::firstOrNew([
            'email' => Arr::get($config, 'email', 'admin@homeat.com'),
        ]);

        $admin->forceFill([
            'name' => Arr::get($config, 'name', 'Super Admin'),
            'role' => 'admin',
            'notelp' => Arr::get($config, 'phone'),
            'alamat' => Arr::get($config, 'address'),
        ]);

        if ($profilePhoto = Arr::get($config, 'profile_photo')) {
            $admin->profile_photo = $profilePhoto;
        }

    $admin->password = Hash::make($password);

    $shouldVerify = filter_var(Arr::get($config, 'verify_email', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $admin->email_verified_at = $shouldVerify !== false ? now() : null;

        if (!$admin->remember_token) {
            $admin->remember_token = Str::random(40);
        }

        $admin->save();

        return $admin;
    }

    public function syncDefaultUser(): User
    {
        $config = Config::get('admin.user_seed', []);
        $password = Arr::get($config, 'password');

        if (blank($password)) {
            throw new \RuntimeException('USER_DEFAULT_PASSWORD must be set in the environment.');
        }

        $user = User::firstOrNew([
            'email' => Arr::get($config, 'email', 'user@homeat.com'),
        ]);

        $user->forceFill([
            'name' => Arr::get($config, 'name', 'User Biasa'),
            'role' => 'customer',
        ]);

        $user->password = Hash::make($password);
        $user->email_verified_at = now();

        if (!$user->remember_token) {
            $user->remember_token = Str::random(40);
        }

        $user->save();

        return $user;
    }
}
