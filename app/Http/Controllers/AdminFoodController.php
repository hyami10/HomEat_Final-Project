<?php

namespace App\Http\Controllers;
use App\Models\Food; 
use App\Models\OrderItem;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminFoodController extends Controller
{
    public function index()
    {
        $foods = Food::latest()->get();
        return view('admin.foods.index', compact('foods'));
    }

    public function create()
    {
        return view('admin.foods.create');
    }

    public function store(Request $request)
    {
        $maxPrice = min(999999999, PHP_INT_MAX);
        $maxStock = min(999999, PHP_INT_MAX);
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', "max:{$maxPrice}"],
            'stock' => ['required', 'integer', 'min:0', "max:{$maxStock}"],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'price.max' => 'Harga terlalu besar. Maksimal Rp ' . number_format($maxPrice, 0, ',', '.'),
            'stock.max' => 'Stok terlalu besar. Maksimal ' . number_format($maxStock, 0, ',', '.'),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            \Log::info('Food image upload attempt', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
            $validation = FileUploadService::validateFile($file, ['image/jpeg', 'image/png'], FileUploadService::SIZE_FOOD_IMAGE);
            if (!$validation['valid']) {
                \Log::error('Food image validation failed', ['error' => $validation['error']]);
                return redirect()->back()->withErrors(['image' => $validation['error']])->withInput();
            }
            
            $filename = FileUploadService::generateSafeFilename('food', $validation['safe_extension']);
            \Log::info('Generated safe filename', ['filename' => $filename]);
            
            $imagePath = $file->storeAs('foods', $filename, 'public');
            
            if ($imagePath === false || empty($imagePath)) {
                \Log::warning('storeAs returned false, trying manual move');
                
                $targetDir = storage_path('app/public/foods');
                @mkdir($targetDir, 0755, true);
                
                try {
                    $file->move($targetDir, $filename);
                    $imagePath = 'foods/' . $filename;
                    \Log::info('Manual move successful', ['path' => $imagePath]);
                } catch (\Exception $e) {
                    \Log::error('Manual move failed', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['image' => 'Gagal menyimpan foto. Silakan coba lagi.'])->withInput();
                }
            }
            
            $fullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($fullPath)) {
                \Log::error('File does not exist after upload', ['path' => $fullPath]);
                return redirect()->back()->withErrors(['image' => 'Foto gagal tersimpan. Silakan coba lagi.'])->withInput();
            }
            
            $data['image'] = $imagePath;
            \Log::info('Food image upload successful', [
                'path' => $imagePath,
                'filename' => $filename,
                'file_size' => filesize($fullPath),
            ]);
        }

        $food = Food::create($data);
        \Log::info('Food created', [
            'id' => $food->id,
            'name' => $food->name,
            'image' => $food->image,
        ]);
        
        return redirect()->route('foods.index')->with('success', 'Menu berhasil ditambahkan!');

    }

    public function edit(Food $food)
    {
        return view('admin.foods.edit', compact('food'));
    }

    public function update(Request $request, Food $food)
    {
        $maxPrice = min(999999999, PHP_INT_MAX);
        $maxStock = min(999999, PHP_INT_MAX);
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', "max:{$maxPrice}"],
            'stock' => ['required', 'integer', 'min:0', "max:{$maxStock}"],
            'category' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'price.max' => 'Harga terlalu besar. Maksimal Rp ' . number_format($maxPrice, 0, ',', '.'),
            'stock.max' => 'Stok terlalu besar. Maksimal ' . number_format($maxStock, 0, ',', '.'),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            \Log::info('Food image update attempt', [
                'food_id' => $food->id,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
            
            $validation = FileUploadService::validateFile($file, ['image/jpeg', 'image/png'], FileUploadService::SIZE_FOOD_IMAGE);
            if (!$validation['valid']) {
                \Log::error('Food image validation failed', ['error' => $validation['error']]);
                return redirect()->back()->withErrors(['image' => $validation['error']])->withInput();
            }
            
            if ($food->image) {
                Storage::disk('public')->delete($food->image);
                \Log::info('Old image deleted', ['path' => $food->image]);
            }
            
            $filename = FileUploadService::generateSafeFilename('food', $validation['safe_extension']);
            \Log::info('Generated safe filename', ['filename' => $filename]);
            
            $imagePath = $file->storeAs('foods', $filename, 'public');
            
            if ($imagePath === false || empty($imagePath)) {
                \Log::warning('storeAs returned false, trying manual move');
                
                $targetDir = storage_path('app/public/foods');
                @mkdir($targetDir, 0755, true);
                
                try {
                    $file->move($targetDir, $filename);
                    $imagePath = 'foods/' . $filename;
                    \Log::info('Manual move successful', ['path' => $imagePath]);
                } catch (\Exception $e) {
                    \Log::error('Manual move failed', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['image' => 'Gagal menyimpan foto. Silakan coba lagi.'])->withInput();
                }
            }
            
            $fullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($fullPath)) {
                \Log::error('File does not exist after upload', ['path' => $fullPath]);
                return redirect()->back()->withErrors(['image' => 'Foto gagal tersimpan. Silakan coba lagi.'])->withInput();
            }
            
            $data['image'] = $imagePath;
            \Log::info('Food image update successful', [
                'path' => $imagePath,
                'filename' => $filename,
                'file_size' => filesize($fullPath),
            ]);
        }

        $food->update($data);
        \Log::info('Food updated', [
            'id' => $food->id,
            'name' => $food->name,
            'image' => $food->image,
        ]);
        
        return redirect()->route('foods.index')->with('success', 'Menu berhasil diupdate!');

    }

    public function destroy(Food $food)
    {
        try {
            return DB::transaction(function () use ($food) {
                $food = Food::where('id', $food->id)->lockForUpdate()->first();
                
                if (!$food) {
                    throw new \Exception('Menu tidak ditemukan.');
                }
                
                $activeOrderCount = OrderItem::whereHas('order', function ($query) {
                    $query->whereIn('status', ['pending', 'cooking', 'delivery']);
                })->where('food_id', $food->id)->lockForUpdate()->count();
                
                if ($activeOrderCount > 0) {
                    throw new \Exception(
                        "Tidak dapat menghapus menu ini karena ada {$activeOrderCount} pesanan aktif yang mengandung menu ini. Selesaikan pesanan terlebih dahulu."
                    );
                }
                
                $imagePath = $food->image;
                
                $food->delete();
                
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }

                return redirect()->route('foods.index')->with('success', 'Menu berhasil dihapus.');
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
