<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function updating(User $user): void
    {
        if ($user->isDirty('profile_photo')) {
            $oldPhoto = $user->getOriginal('profile_photo');
            
            if ($oldPhoto && $oldPhoto !== '0' && $oldPhoto !== $user->profile_photo) {
                if (Storage::disk('public')->exists($oldPhoto)) {
                    Storage::disk('public')->delete($oldPhoto);
                    Log::info('Auto-cleaned old profile photo', [
                        'user_id' => $user->id,
                        'old_photo' => $oldPhoto,
                        'new_photo' => $user->profile_photo,
                    ]);
                }
            }
        }
    }

    public function deleting(User $user): void
    {
        if ($user->profile_photo && $user->profile_photo !== '0') {
            if (Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
                Log::info('Auto-cleaned profile photo on user deletion', [
                    'user_id' => $user->id,
                    'photo' => $user->profile_photo,
                ]);
            }
        }
    }
}
