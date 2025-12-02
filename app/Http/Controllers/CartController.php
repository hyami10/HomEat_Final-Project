<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('food')->get();
        return view('carts.index', compact('cartItems'));
    }

    public function store(Request $request, Food $food)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk menambahkan item ke keranjang.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'quantity' => 'nullable|integer|min:1|max:999'
        ], [
            'quantity.integer' => 'Jumlah harus berupa angka bulat!',
            'quantity.min' => 'Jumlah minimal 1!',
            'quantity.max' => 'Jumlah maksimal 999!',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return redirect()->back()->withErrors($validator)->with('error', $validator->errors()->first());
        }

        $qtyRequest = (int) $request->input('quantity', 1);

        return DB::transaction(function () use ($qtyRequest, $food, $request) {
            // Lock food untuk mencegah race condition
            $food = Food::where('id', $food->id)->lockForUpdate()->first();
            
            if (!$food) {
                $msg = 'Menu tidak ditemukan!';
                return $request->expectsJson() 
                    ? response()->json(['success' => false, 'message' => $msg], 404)
                    : redirect()->back()->with('error', $msg);
            }
            
            $existingCart = Cart::where('user_id', Auth::id())
                                ->where('food_id', $food->id)
                                ->lockForUpdate()
                                ->first();
            
            $currentQtyInCart = $existingCart ? $existingCart->quantity : 0;
            $totalQtyNanti = $currentQtyInCart + $qtyRequest;
            
            if ($totalQtyNanti > $food->stock) {
                $maxCanAdd = $food->stock - $currentQtyInCart;
                $message = $currentQtyInCart > 0 
                    ? "Stok tidak cukup! Di keranjang: {$currentQtyInCart}, Tersisa: {$food->stock}, Maksimal tambah: {$maxCanAdd}"
                    : "Stok tidak mencukupi! Sisa stok: {$food->stock}";
                    
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : redirect()->back()->with('error', $message);
            }
            
            if ($existingCart) {
                $existingCart->increment('quantity', $qtyRequest);
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'food_id' => $food->id,
                    'quantity' => $qtyRequest, 
                ]);
            }
            
            cache()->forget("cart_count_" . Auth::id());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item berhasil ditambahkan ke keranjang!',
                    'cart_count' => Cart::where('user_id', Auth::id())->count()
                ]);
            }

            return redirect()->back()->with('success', 'Item berhasil ditambahkan ke keranjang!');
        });
    }

    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) { abort(403); }

        $request->validate([
            'type' => 'required|string|in:plus,minus'
        ]);

        return DB::transaction(function () use ($request, $cart) {
            $cart = Cart::where('id', $cart->id)->lockForUpdate()->first();
            $cart->load('food');
            $food = $cart->food;
            $food->refresh();
            
            if ($request->type === 'plus') {
                if ($cart->quantity < $food->stock) {
                    $cart->quantity++;
                } else {
                    return redirect()->back()->with('error', 'Stok mentok! Tidak bisa tambah lagi.');
                }
            } elseif ($request->type === 'minus') {
                if ($cart->quantity > 1) {
                    $cart->quantity--;
                }
            }

            $cart->save();
            
            cache()->forget("cart_count_" . Auth::id());
            
            return redirect()->back();
        });
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) { abort(403); }
        $cart->delete();
        
        cache()->forget("cart_count_" . Auth::id());
        
        return redirect()->back()->with('success', 'Item dihapus.');
    }
}