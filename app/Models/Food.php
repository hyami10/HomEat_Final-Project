<?php

namespace App\Models;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods'; 

    protected $fillable = [
        'name',
        'price',
        'stock',
        'category',
        'image',
        'description',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return asset('storage/' . $this->image);
    }

    protected static function booted(): void
    {
        $flushCache = static function (): void {
            Cache::forget('dashboard:foods:all');
        };

        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
