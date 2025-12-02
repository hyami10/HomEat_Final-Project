<?php

use App\Http\Controllers\AdminFoodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController; 
use App\Http\Controllers\AdminOrderController; 
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Food;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function (Request $request) {
    $request->validate([
        'search' => ['nullable', 'string', 'max:255'],
    ]);

    $search = trim((string) $request->input('search', ''));

    $baseQuery = Food::query()->select(['id', 'name', 'price', 'stock', 'category', 'image'])->orderBy('name');

    if ($search === '') {
        $foods = $baseQuery->get();
    } else {
        $escaped = addcslashes($search, '%_\\');
        $foods = (clone $baseQuery)->where('name', 'like', "%{$escaped}%")->get();
    }

    return view('dashboard', compact('foods'));
})->middleware('throttle:60,1')->name('dashboard');


Route::get('/menu/{food}', [MenuController::class, 'show'])->name('menu.show');

Route::middleware(['auth'])->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateAlamatNotelp'])
        ->middleware('throttle:10,1')
        ->name('profile.updateAlamatNotelp');
    Route::patch('/profile/edit', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('profile.update'); 
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:5,1')
        ->name('profile.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('carts.index');
    Route::post('/cart/{food}', [CartController::class, 'store'])
        ->middleware('throttle:30,1') // Rate limit: 30 cart operations per minute
        ->name('carts.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])
        ->middleware('throttle:60,1')
        ->name('carts.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('carts.destroy');

    Route::post('/checkout', [OrderController::class, 'checkout'])
        ->middleware('throttle:10,1') // Rate limit: 10 checkouts per minute
        ->name('orders.checkout');
    Route::get('/tracking-order', [OrderController::class, 'track'])->name('orders.track');
    Route::patch('/orders/{order}/complete', [OrderController::class, 'markAsCompleted'])->name('orders.complete');
    
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
});

Route::middleware(['auth'])->group(function () {
    Route::patch('/orders/{order}/upload-proof', [OrderController::class, 'uploadProof'])
        ->middleware('throttle:10,1') // Rate limit: 10 uploads per minute
        ->name('orders.uploadProof');
});


Route::middleware(['auth', 'admin', 'throttle:60,1'])->group(function () {
    
    Route::resource('foods', AdminFoodController::class);

    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::patch('/admin/orders/{order}', [AdminOrderController::class, 'update'])->name('admin.orders.update');
    
    Route::patch('/admin/orders/{order}/reject', [AdminOrderController::class, 'rejectOrder'])->name('admin.orders.reject');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/verify', [AdminUserController::class, 'verify'])->name('admin.users.verify');
    
});

require __DIR__.'/auth.php';