<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private const TAX_RATE = 0.11;
    private const DELIVERY_FEE = 10000;
    public function checkout(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Admin tidak diperbolehkan melakukan pemesanan!');
        }

        if (is_null($user->email_verified_at)) {
            return redirect()->route('orders.track')->with('error', 'Akun Anda belum diverifikasi oleh admin. Mohon tunggu persetujuan.');
        }

        $alamatPengiriman = $user->alamat ?? $user->address;
        if (empty($alamatPengiriman)) {
             return redirect()->route('profile.edit')->with('error', 'Mohon isi alamat terlebih dahulu!');
        }

        $request->validate([
            'payment_method' => 'required|string|in:COD,Transfer'
        ]);
        $paymentMethod = strip_tags($request->input('payment_method'));

        try {
            return DB::transaction(function () use ($user, $alamatPengiriman, $paymentMethod) {
                $cartItems = Cart::where('user_id', $user->id)->get(); 
                
                if ($cartItems->isEmpty()) {
                    throw new \Exception('Keranjang kosong.');
                }

                $cartItemIds = $cartItems->pluck('food_id')->toArray();
                $foods = Food::whereIn('id', $cartItemIds)->lockForUpdate()->get()->keyBy('id');
                
                $subtotal = 0;
                $orderItemsData = [];

                foreach ($cartItems as $item) {
                    $food = $foods->get($item->food_id);
                    
                    if (!$food) {
                        throw new \Exception('Salah satu menu di keranjang sudah tidak tersedia!');
                    }
                    if ($item->quantity > $food->stock) {
                        throw new \Exception("Stok {$food->name} tidak cukup! Tersisa: {$food->stock}");
                    }

                    $lineTotal = $food->price * $item->quantity;
                    $subtotal += $lineTotal;

                    $orderItemsData[] = [
                        'food' => $food,
                        'quantity' => $item->quantity,
                        'price' => $food->price,
                        'subtotal' => $lineTotal
                    ];
                }
                
                $tax = $subtotal * self::TAX_RATE;
                $total = $subtotal + $tax + self::DELIVERY_FEE;

                $order = Order::create([
                    'user_id' => $user->id,
                    'shipping_address' => $alamatPengiriman,
                    'total_price' => $total,
                    'status' => 'pending', 
                    'payment_method' => $paymentMethod, 
                ]);

                foreach ($orderItemsData as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'food_id' => $data['food']->id,
                        'food_name' => $data['food']->name,
                        'food_price' => $data['price'],
                        'quantity' => $data['quantity'],
                        'subtotal' => $data['subtotal'],
                    ]);
                    
                    $data['food']->decrement('stock', $data['quantity']);
                }

                Cart::where('user_id', $user->id)->delete();

                return redirect()->route('orders.track')->with('order_success', true);
            });
        } catch (\Exception $e) {
            // Whitelist user-safe error messages
            $userSafeKeywords = ['Keranjang kosong', 'Stok', 'tidak tersedia', 'tidak ditemukan'];
            $isUserSafe = false;
            $errorMsg = $e->getMessage();
            
            foreach ($userSafeKeywords as $keyword) {
                if (str_contains($errorMsg, $keyword)) {
                    $isUserSafe = true;
                    break;
                }
            }
            
            if ($isUserSafe) {
                $msg = $errorMsg;
            } else {
                $msg = 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi admin.';
                \Illuminate\Support\Facades\Log::error('Checkout error', [
                    'error' => $errorMsg,
                    'user_id' => $user->id ?? null,
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return redirect()->route('carts.index')->with('error', $msg);
        }
    }

    public function track()
    {
        $orders = Order::where('user_id', Auth::id())->with('items')->latest()->get();
        return view('orders.track', compact('orders'));
    }

    public function markAsCompleted(Order $order)
    {
        if ($order->user_id !== Auth::id()) { abort(403); }

        if ($order->status == 'delivery') {
            $order->update(['status' => 'completed']);
            return redirect()->back()->with('success', 'Terima kasih! Pesanan telah diterima.');
        }
        return redirect()->back();
    }

    public function cancelOrder(Order $order)
    {
        if ($order->user_id !== Auth::id()) { 
            abort(403); 
        }

        try {
            DB::transaction(function () use ($order) {
                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                
                if ($lockedOrder->status == 'pending') {
                    foreach ($lockedOrder->items as $item) {
                        if ($item->food_id) {
                            $food = Food::where('id', $item->food_id)->lockForUpdate()->first();
                            if ($food) {
                                $food->increment('stock', $item->quantity);
                            }
                        }
                    }
                    
                    $lockedOrder->update(['status' => 'cancelled']);
                } else {
                    throw new \Exception('Pesanan sudah diproses, tidak bisa dibatalkan.');
                }
            });
            
            return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan. Stok telah dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function uploadProof(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $maxSizeKB = \App\Services\FileUploadService::SIZE_PAYMENT_PROOF;
        
        $request->validate([
            'payment_proof' => "required|file|mimes:jpeg,png,jpg|max:{$maxSizeKB}",
        ]);

        $file = $request->file('payment_proof');
        
        $validation = \App\Services\FileUploadService::validateFile(
            $file, 
            ['image/jpeg', 'image/png'],
            $maxSizeKB
        );
        if (!$validation['valid']) {
            return redirect()->back()->with('error', $validation['error']);
        }

        $filename = \App\Services\FileUploadService::generateSafeFilename('proof_' . $order->id, $validation['safe_extension']);
        
        $targetDir = storage_path('app/public/payment_proofs');
        
        @mkdir($targetDir, 0750, true);
        
        try {
            $file->move($targetDir, $filename);
            $path = 'payment_proofs/' . $filename;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Move failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan file. Masalah permission server.');
        }

        $affected = DB::table('orders')
            ->where('id', $order->id)
            ->update(['payment_proof' => $path]);

        if ($affected === 0) {
             \Illuminate\Support\Facades\Log::error("Failed to update payment_proof for Order ID: " . $order->id);
        }

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Mohon tunggu verifikasi admin.');
    }
}