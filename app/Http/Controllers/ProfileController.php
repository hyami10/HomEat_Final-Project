<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function updateAlamatNotelp(Request $request): RedirectResponse
    {
        $request->validate([
            'alamat' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\.\-\,\/\#\(\)\&\:]+$/'], 
            'notelp' => ['nullable', 'digits_between:10,13'],
        ], [
            'alamat.regex' => 'Format alamat tidak valid. Karakter yang diizinkan: huruf, angka, spasi, . - , / # ( ) & :',
            'notelp.digits_between' => 'Nomor telepon harus antara 10 sampai 13 angka.',
        ]);

        $user = $request->user();
        $user->alamat = $request->alamat;
        $user->notelp = $request->notelp;
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'address-updated');
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->has('alamat')) {
            $user->alamat = $request->alamat;
        }
        if ($request->has('notelp')) {
            $user->notelp = $request->notelp;
        }

        if ($request->boolean('remove_profile_photo') && $user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->profile_photo = null;
        }


        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            
            Log::info('Profile photo upload attempt', [
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
            
            $validation = FileUploadService::validateFile($file, ['image/jpeg', 'image/png']);
            if (!$validation['valid']) {
                Log::error('Profile photo validation failed', [
                    'user_id' => $user->id,
                    'error' => $validation['error'],
                ]);
                return redirect()->back()->withErrors(['profile_photo' => $validation['error']])->withInput();
            }

            $filename = FileUploadService::generateSafeFilename('profile_' . $user->id, $validation['safe_extension']);
            $destinationPath = storage_path('app/public/profiles');
            
            try {
                @mkdir($destinationPath, 0755, true);
                
                $file->move($destinationPath, $filename);
                $photoPath = 'profiles/' . $filename;
                
                Log::info('Profile photo uploaded successfully', [
                    'user_id' => $user->id,
                    'path' => $photoPath,
                    'filename' => $filename,
                ]);
                
                $user->profile_photo = $photoPath;
                
            } catch (\Exception $e) {
                Log::error('Profile photo upload failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'filename' => $filename,
                ]);
                return redirect()->back()->withErrors(['profile_photo' => 'Gagal menyimpan foto. Silakan coba lagi.'])->withInput();
            }
        }

        $user->save();
        
        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password', 'max:72'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
