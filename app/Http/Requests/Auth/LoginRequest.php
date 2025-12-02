<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:72'], // CRITICAL: Prevent DOS via hash calculation
        ];
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $lockoutKey = 'login_lockout:' . $this->ip();
        $totalFailedAttempts = (int) RateLimiter::attempts($lockoutKey);

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            // Track for progressive lockout with progressive decay time
            $decaySeconds = $this->getProgressiveLockoutDecay($totalFailedAttempts + 1);
            RateLimiter::hit($lockoutKey, $decaySeconds);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($lockoutKey);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $lockoutKey = 'login_lockout:' . $this->ip();
        $totalFailedAttempts = (int) RateLimiter::attempts($lockoutKey);
        
        if ($totalFailedAttempts >= 10) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            if ($seconds > 0) {
                throw ValidationException::withMessages([
                    'email' => trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }
        }
        
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function getProgressiveLockoutDecay(int $failedAttempts): int
    {
        if ($failedAttempts < 10) {
            return 3600; 
        }

        $lockoutMinutes = min(1 << intdiv($failedAttempts, 5), 30);
        return $lockoutMinutes * 60;
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
